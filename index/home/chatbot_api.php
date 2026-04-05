<?php
/**
 * chatbot_api.php
 * Chatbot backend — returns JSON responses using real DB data.
 * PRIVACY: Never exposes email, phone, address, dob, license numbers,
 *          emergency contacts, or any personal member details.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once('../../config/db_connect.php');

$input   = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim(strtolower($input['message'])) : '';

if ($message === '') {
    echo json_encode(['reply' => 'Please type a message. / Mangyaring mag-type ng mensahe.', 'type' => 'text']);
    exit;
}

/* ══════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════ */
function safe($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
function formatDate($d) {
    return $d ? date('F j, Y', strtotime($d)) : 'TBA';
}
function formatTime($t) {
    return $t ? date('g:i A', strtotime($t)) : '';
}
function detect($msg, array $keywords): bool {
    foreach ($keywords as $kw) {
        if (strpos($msg, $kw) !== false) return true;
    }
    return false;
}

/**
 * Try to extract a date from the message.
 * Supports formats like:
 *   "april 10 2026", "apr 10, 2026", "10/04/2026", "2026-04-10",
 *   "april 10", "apr 10", "10 april 2026"
 * Returns a Y-m-d string or null.
 */
function extractDate(string $msg): ?string {
    // ISO / numeric: 2026-04-10 or 04/10/2026 or 10/04/2026
    if (preg_match('/\b(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\b/', $msg, $m)) {
        $d = @mktime(0,0,0,(int)$m[2],(int)$m[3],(int)$m[1]);
        if ($d) return date('Y-m-d', $d);
    }
    if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $msg, $m)) {
        $d = @mktime(0,0,0,(int)$m[1],(int)$m[2],(int)$m[3]);
        if ($d) return date('Y-m-d', $d);
    }
    // "April 10, 2026" / "Apr 10 2026" / "10 April 2026"
    if (preg_match('/\b(\d{1,2})\s+(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?),?\s+(\d{4})\b/i', $msg, $m)) {
        $d = @strtotime($m[1].' '.$m[2].' '.$m[3]);
        if ($d) return date('Y-m-d', $d);
    }
    if (preg_match('/\b(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s+(\d{1,2}),?\s+(\d{4})\b/i', $msg, $m)) {
        $d = @strtotime($m[1].' '.$m[2].' '.$m[3]);
        if ($d) return date('Y-m-d', $d);
    }
    // "April 10" or "Apr 10" (no year — assume current year, or next year if past)
    if (preg_match('/\b(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s+(\d{1,2})\b/i', $msg, $m)) {
        $year = date('Y');
        $d = @strtotime($m[1].' '.$m[2].' '.$year);
        if ($d && $d < time()) $d = @strtotime($m[1].' '.$m[2].' '.($year+1));
        if ($d) return date('Y-m-d', $d);
    }
    return null;
}

$reply  = '';
$type   = 'text';
$action = null;

/* ══════════════════════════════════════════════
   LANGUAGE DETECTION
   — Tagalog if message contains Tagalog markers;
     otherwise default to English.
══════════════════════════════════════════════ */
$tagalogMarkers = [
    'kumusta','kamusta','musta','magandang','salamat','maraming',
    'paano','saan','nasaan','kailan','sino','ano ang','anong',
    'may event','may aktibidad','may bago','anong balita','anunsyo',
    'mga opisyal','mga officer','miyembro','kasapi','sumali',
    'opisina','bayad','tulong','gabay','susunod','aktibidad',
    'ilan','ilang','naman','po','ba','kaya','pwede','pano',
    'gusto ko','hindi','walang','narito','tingnan','bumalik',
    'binago','balita','bago','lakad','plano','gawain'
];
$isTagalog = detect($message, $tagalogMarkers);

/* ══════════════════════════════════════════════
   INTENT MATCHING
══════════════════════════════════════════════ */

/* ── SPECIFIC DATE LOOKUP (check first) ── */
$parsedDate = extractDate($message);
if ($parsedDate && detect($message, [
    'event','aktibidad','activity','announcement','anunsyo','balita',
    'meron','may','what','anong','anything','something','schedule',
    'nangyayari','nangyari','magaganap','araw na ito','petsa'
])) {
    $displayDate = date('F j, Y', strtotime($parsedDate));
    $found = false;

    // Search events
    $stmt = $conn->prepare(
        "SELECT event_name, time, location FROM events
         WHERE date = ? AND is_archived = 0
           AND (category IS NULL OR category != 'Officers Meeting')
         ORDER BY time ASC"
    );
    $stmt->bind_param("s", $parsedDate);
    $stmt->execute();
    $evResult = $stmt->get_result();

    // Search announcements
    $stmt2 = $conn->prepare(
        "SELECT title, content FROM announcements
         WHERE date_posted = ?
           AND (expiry_date IS NULL OR expiry_date >= CURDATE())
         ORDER BY id DESC LIMIT 3"
    );
    $stmt2->bind_param("s", $parsedDate);
    $stmt2->execute();
    $anResult = $stmt2->get_result();

    if ($isTagalog) {
        $reply = "Narito ang nahanap ko para sa <strong>{$displayDate}</strong>:<br><br>";
        $noResult = "Wala akong nahanap na event o anunsyo para sa <strong>{$displayDate}</strong>.";
    } else {
        $reply = "Here's what I found for <strong>{$displayDate}</strong>:<br><br>";
        $noResult = "I couldn't find any events or announcements for <strong>{$displayDate}</strong>.";
    }

    if ($evResult && $evResult->num_rows > 0) {
        $found = true;
        $reply .= $isTagalog ? "<strong>📅 Events:</strong><br>" : "<strong>📅 Events:</strong><br>";
        while ($row = $evResult->fetch_assoc()) {
            $reply .= "<div style='margin-bottom:8px;padding:8px 10px;background:#f0f4ff;border-left:3px solid #1a56db;border-radius:6px;font-size:13px;'>";
            $reply .= "<strong>" . safe($row['event_name']) . "</strong>";
            if ($row['time']) $reply .= "<br><span style='color:#555;'>" . formatTime($row['time']) . "</span>";
            if ($row['location']) $reply .= "<br><span style='color:#777;font-size:12px;'>" . safe($row['location']) . "</span>";
            $reply .= "</div>";
        }
        $action = ['label' => $isTagalog ? 'Tingnan ang Events' : 'View Events', 'url' => 'events.php'];
    }

    if ($anResult && $anResult->num_rows > 0) {
        $found = true;
        $reply .= $isTagalog ? "<strong>📢 Mga Anunsyo:</strong><br>" : "<strong>📢 Announcements:</strong><br>";
        while ($row = $anResult->fetch_assoc()) {
            $preview = mb_strlen($row['content']) > 80 ? mb_substr($row['content'], 0, 80) . '...' : $row['content'];
            $reply .= "<div style='margin-bottom:8px;padding:8px 10px;background:#fff8e1;border-left:3px solid #f59e0b;border-radius:6px;font-size:13px;'>";
            $reply .= "<strong>" . safe($row['title']) . "</strong><br>";
            $reply .= "<span style='color:#444;'>" . safe($preview) . "</span>";
            $reply .= "</div>";
        }
        if (!$action) $action = ['label' => $isTagalog ? 'Tingnan ang Anunsyo' : 'View Announcements', 'url' => 'announcement.php'];
    }

    if (!$found) {
        $reply = $noResult;
    }
    $type = 'html';

/* ── GREETING ── */
} elseif (detect($message, [
    'hello','hi','hey','good morning','good afternoon','good evening',
    'kumusta','magandang umaga','magandang hapon','magandang gabi',
    'magandang araw','kamusta','musta','sup','yo'
])) {
    if ($isTagalog) {
        $greetings = [
            "Kamusta! Maligayang pagdating sa portal ng Bankero and Fisherman Association. Paano kita matutulungan ngayon?",
            "Hello! Ako ang BFA Virtual Assistant. Pwede mo akong tanungin tungkol sa aming mga events, opisyal, balita, o membership.",
            "Magandang araw! Ano ang maipaglilingkod ko para sa iyo ngayon?"
        ];
    } else {
        $greetings = [
            "Hello! Welcome to the Bankero and Fisherman Association portal. How can I help you today?",
            "Hi there! I'm the BFA Virtual Assistant. You can ask me about events, officers, announcements, or membership.",
            "Good day! What can I do for you today?"
        ];
    }
    $reply = $greetings[array_rand($greetings)];

/* ── NEXT EVENT (specific — check before general events) ── */
} elseif (detect($message, [
    'next event','susunod na event','next activity','susunod na aktibidad',
    'next meeting','susunod na pulong','anong susunod','kailan susunod',
    'may event ba','meron bang event','may aktibidad ba'
])) {
    $result = $conn->query(
        "SELECT event_name, description, date, time, location
         FROM events
         WHERE date >= CURDATE() AND is_archived = 0
           AND (category IS NULL OR category != 'Officers Meeting')
         ORDER BY date ASC LIMIT 1"
    );
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($isTagalog) {
            $reply = "Ang <strong>susunod na event</strong> ng asosasyon ay:<br><br>";
            $actionLabel = 'Tingnan ang Events';
            $noResult    = "Wala pang susunod na event sa ngayon. Mangyaring bumalik at tingnan muli sa ibang pagkakataon.";
        } else {
            $reply = "The <strong>next upcoming event</strong> of the association is:<br><br>";
            $actionLabel = 'View Events';
            $noResult    = "There are no upcoming events at the moment. Please check back later.";
        }
        $reply .= "<div style='padding:8px 10px;background:#f0f4ff;border-left:3px solid #1a56db;border-radius:6px;font-size:13px;'>"
               . "<strong>" . safe($row['event_name']) . "</strong><br>"
               . "<span style='color:#555;'>" . formatDate($row['date']);
        if ($row['time']) $reply .= " &bull; " . formatTime($row['time']);
        $reply .= "</span><br>";
        if ($row['location']) $reply .= "<span style='color:#777;font-size:12px;'>" . safe($row['location']) . "</span>";
        $reply .= "</div>";
        $type   = 'html';
        $action = ['label' => $actionLabel, 'url' => 'events.php'];
    } else {
        $reply = $isTagalog
            ? "Wala pang susunod na event sa ngayon. Mangyaring bumalik at tingnan muli sa ibang pagkakataon."
            : "There are no upcoming events at the moment. Please check back later.";
    }

/* ── EVENTS (general) ── */
} elseif (detect($message, [
    'event','aktibidad','activity','upcoming','schedule','sched',
    'kailan','when','programa','gawain','lakad','plano',
    'anong meron','anong events','anong aktibidad','listahan ng event'
])) {
    $result = $conn->query(
        "SELECT event_name, description, date, time, location, category
         FROM events
         WHERE date >= CURDATE() AND is_archived = 0
           AND (category IS NULL OR category != 'Officers Meeting')
         ORDER BY date ASC LIMIT 3"
    );
    if ($result && $result->num_rows > 0) {
        $reply = $isTagalog
            ? "Narito ang mga <strong>paparating na events</strong> ng asosasyon:<br><br>"
            : "Here are the <strong>upcoming events</strong> of the association:<br><br>";
        while ($row = $result->fetch_assoc()) {
            $reply .= "<div style='margin-bottom:10px;padding:8px 10px;background:#f0f4ff;border-left:3px solid #1a56db;border-radius:6px;font-size:13px;'>";
            $reply .= "<strong>" . safe($row['event_name']) . "</strong><br>";
            $reply .= "<span style='color:#555;'>" . formatDate($row['date']);
            if ($row['time']) $reply .= " &bull; " . formatTime($row['time']);
            $reply .= "</span><br>";
            if ($row['location']) $reply .= "<span style='color:#777;font-size:12px;'>" . safe($row['location']) . "</span>";
            $reply .= "</div>";
        }
        $type   = 'html';
        $action = ['label' => $isTagalog ? 'Tingnan Lahat ng Events' : 'View All Events', 'url' => 'events.php'];
    } else {
        $reply  = $isTagalog
            ? "Wala pang paparating na events sa ngayon. Mangyaring bumalik at tingnan muli mamaya."
            : "No upcoming events at the moment. Please check back later.";
        $action = ['label' => 'Events Page', 'url' => 'events.php'];
    }

/* ── ANNOUNCEMENTS ── */
} elseif (detect($message, [
    'announcement','balita','news','update','latest','recent','notice',
    'anunsyo','abiso','anong bago','may bago ba','pinakabago',
    'anong balita','may announcement','anong notice'
])) {
    $result = $conn->query(
        "SELECT title, content, date_posted, category
         FROM announcements
         WHERE (expiry_date IS NULL OR expiry_date >= CURDATE())
         ORDER BY date_posted DESC LIMIT 3"
    );
    if ($result && $result->num_rows > 0) {
        $reply = $isTagalog
            ? "Narito ang mga <strong>pinakabagong anunsyo</strong>:<br><br>"
            : "Here are the <strong>latest announcements</strong>:<br><br>";
        while ($row = $result->fetch_assoc()) {
            $preview = mb_strlen($row['content']) > 100
                ? mb_substr($row['content'], 0, 100) . '...'
                : $row['content'];
            $reply .= "<div style='margin-bottom:10px;padding:8px 10px;background:#f0f4ff;border-left:3px solid #1a56db;border-radius:6px;font-size:13px;'>";
            $reply .= "<strong>" . safe($row['title']) . "</strong><br>";
            $reply .= "<span style='color:#777;font-size:12px;'>" . formatDate($row['date_posted']) . "</span><br>";
            $reply .= "<span style='color:#444;'>" . safe($preview) . "</span>";
            $reply .= "</div>";
        }
        $type   = 'html';
        $action = ['label' => $isTagalog ? 'Tingnan Lahat ng Anunsyo' : 'View All Announcements', 'url' => 'announcement.php'];
    } else {
        $reply  = $isTagalog
            ? "Wala pang anunsyo sa ngayon. Mangyaring bumalik at tingnan muli mamaya."
            : "No announcements at the moment. Please check back later.";
        $action = ['label' => 'Announcements', 'url' => 'announcement.php'];
    }

/* ── OFFICERS ── */
} elseif (detect($message, [
    'officer','opisyal','president','vice','secretary','treasurer','auditor',
    'board','leadership','sino ang','sino yung','sino ang pangulo',
    'mga opisyal','mga officer','pamunuan','lider','pangulo','kalihim',
    'ingat-yaman','tagapayo','pangkat','namumuno','namamahala'
])) {
    $result = $conn->query(
        "SELECT o.position, m.name, o.term_start, o.term_end
         FROM officers o
         JOIN members m ON o.member_id = m.id
         WHERE o.term_end >= CURDATE()
         ORDER BY o.id ASC LIMIT 8"
    );
    if ($result && $result->num_rows > 0) {
        $reply = $isTagalog
            ? "Narito ang mga <strong>kasalukuyang opisyal</strong> ng Bankero and Fisherman Association:<br><br>"
            : "Here are the <strong>current officers</strong> of the Bankero and Fisherman Association:<br><br>";
        $reply .= "<div style='font-size:13px;'>";
        while ($row = $result->fetch_assoc()) {
            $reply .= "<div style='padding:5px 0;border-bottom:1px solid #eee;'>";
            $reply .= "<strong style='color:#1a56db;'>" . safe($row['position']) . "</strong>";
            $reply .= " &mdash; " . safe($row['name']);
            $reply .= "</div>";
        }
        $reply .= "</div>";
        $type   = 'html';
        $action = ['label' => $isTagalog ? 'Tingnan ang Officers Page' : 'View Officers Page', 'url' => 'about_us.php'];
    } else {
        $reply  = $isTagalog
            ? "Ang impormasyon ng mga opisyal ay kasalukuyang ina-update. Mangyaring bisitahin ang aming About page."
            : "Officer information is currently being updated. Please visit our About page.";
        $action = ['label' => 'About Page', 'url' => 'about_us.php'];
    }

/* ── MEMBER COUNT ── */
} elseif (detect($message, [
    'how many member','total member','member count',
    'ilan ang miyembro','ilang miyembro','ilan kayong miyembro',
    'ilan ang kasapi','gaano karaming miyembro','total na miyembro'
])) {
    $result = $conn->query("SELECT COUNT(*) as total FROM members WHERE membership_status = 'active'");
    $row    = $result ? $result->fetch_assoc() : null;
    $count  = $row ? $row['total'] : 0;
    $reply  = $isTagalog
        ? "Ang Bankero and Fisherman Association ay kasalukuyang may <strong>{$count} aktibong miyembro</strong>. Patuloy na lumalaki ang aming komunidad!"
        : "The Bankero and Fisherman Association currently has <strong>{$count} active members</strong>. Our community continues to grow!";
    $type   = 'html';

/* ── JOIN / MEMBERSHIP ── */
} elseif (detect($message, [
    'join','become a member','how to be a member','mag-member','membership',
    'paano maging miyembro','paano mag-join','paano sumali','gusto ko sumali',
    'register','sign up','enroll','mag-join','pano maging miyembro',
    'pwede bang maging miyembro','requirements','ano ang kailangan',
    'magkano','bayad','membership fee','bayad sa membership'
])) {
    if ($isTagalog) {
        $reply = "Para maging miyembro ng Bankero and Fisherman Association, maaari kang:<br><br>"
               . "<strong>1.</strong> Pumunta sa opisina ng asosasyon sa Olongapo City.<br>"
               . "<strong>2.</strong> Makipag-ugnayan sa sinuman sa aming mga opisyal para sa tulong.<br>"
               . "<strong>3.</strong> Ihanda ang iyong valid ID at basic na personal na impormasyon.<br><br>"
               . "Bukas ang membership para sa lahat ng rehistradong Bangkero at Mangingisda sa lugar.";
        $action = ['label' => 'Makipag-ugnayan sa Opisyal', 'url' => 'contact_us.php'];
    } else {
        $reply = "To become a member of the Bankero and Fisherman Association, you may:<br><br>"
               . "<strong>1.</strong> Visit the association's office in Olongapo City.<br>"
               . "<strong>2.</strong> Contact any of our officers for assistance.<br>"
               . "<strong>3.</strong> Prepare a valid ID and basic personal information.<br><br>"
               . "Membership is open to all registered fishermen and boatmen in the area.";
        $action = ['label' => 'Contact an Officer', 'url' => 'contact_us.php'];
    }
    $type = 'html';

/* ── CONTACT / LOCATION ── */
} elseif (detect($message, [
    'contact','reach','communicate','makipag-ugnayan',
    'saan ang opisina','nasaan','lokasyon','address ng opisina',
    'where','location','pupunta','pumunta','puntahan',
    'paano makipag-ugnayan','numero','telepono ng opisina'
])) {
    if ($isTagalog) {
        $reply  = "Maaari kang makipag-ugnayan sa Bankero and Fisherman Association sa pamamagitan ng aming <strong>Contact page</strong>. Ang aming mga opisyal ay handa kang tulungan sa oras ng opisina sa Olongapo City.";
        $action = ['label' => 'Makipag-ugnayan', 'url' => 'contact_us.php'];
    } else {
        $reply  = "You can reach the Bankero and Fisherman Association through our <strong>Contact page</strong>. Our officers are available during office hours in Olongapo City.";
        $action = ['label' => 'Contact Us', 'url' => 'contact_us.php'];
    }
    $type = 'html';

/* ── ABOUT ── */
} elseif (detect($message, [
    'about','tungkol','what is','ano ang','history','kasaysayan',
    'mission','vision','goals','layunin','association','bfma',
    'ano ang bfma','ano ang asosasyon','sino kayo','tungkol sa inyo',
    'magsabi tungkol','ipakilala','ilarawan'
])) {
    $result = $conn->query("SELECT title, content FROM who_we_are ORDER BY created_at ASC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row     = $result->fetch_assoc();
        $preview = mb_strlen($row['content']) > 200
            ? mb_substr(strip_tags($row['content']), 0, 200) . '...'
            : strip_tags($row['content']);
        $reply  = "<strong>" . safe($row['title']) . "</strong><br><br>" . safe($preview);
        $type   = 'html';
        $action = [
            'label' => $isTagalog ? 'Alamin Pa Ang Tungkol Sa Amin' : 'Learn More About Us',
            'url'   => 'about_us.php'
        ];
    } else {
        if ($isTagalog) {
            $reply  = "Ang Bankero and Fisherman Association (BFA) ay isang organisasyon ng komunidad na naglilingkod sa mga Bangkero at Mangingisda ng Olongapo City, Zambales. Bisitahin ang aming About page para sa karagdagang impormasyon.";
            $action = ['label' => 'Tungkol Sa Amin', 'url' => 'about_us.php'];
        } else {
            $reply  = "The Bankero and Fisherman Association (BFA) is a community organization serving the fishermen and boatmen of Olongapo City, Zambales. Visit our About page for more information.";
            $action = ['label' => 'About Us', 'url' => 'about_us.php'];
        }
    }

/* ── THANK YOU ── */
} elseif (detect($message, [
    'thank','salamat','thanks','maraming salamat','pasalamat',
    'nagpapasalamat','thank you','tyvm','ty'
])) {
    if ($isTagalog) {
        $replies = [
            "Walang anuman! Huwag mag-atubiling magtanong kung may kailangan ka pa. Lagi kaming nandito para tumulong!",
            "Ikinalulugod naming makatulong! May iba ka pa bang gustong malaman?",
            "Walang problema! Kung may iba kang tanong, nandito lang kami."
        ];
    } else {
        $replies = [
            "You're welcome! Feel free to ask if you need anything else. We're always here to help!",
            "Happy to help! Is there anything else you'd like to know?",
            "No problem! If you have more questions, just let me know."
        ];
    }
    $reply = $replies[array_rand($replies)];

/* ── HELP ── */
} elseif (detect($message, [
    'help','tulong','assist','ano pwede','what can you','what do you',
    'ano ang kaya mo','ano ang pwede','paano ka makakatulong',
    'anong tanong','pwede ba','magtanong','gabay'
])) {
    if ($isTagalog) {
        $reply = "Narito ang mga bagay na kaya kong sagutin:<br><br>"
               . "&bull; <strong>Events / Aktibidad</strong> — mga paparating na gawain<br>"
               . "&bull; <strong>Anunsyo / Balita</strong> — pinakabagong updates<br>"
               . "&bull; <strong>Mga Opisyal</strong> — kasalukuyang pamunuan<br>"
               . "&bull; <strong>Membership</strong> — paano sumali sa asosasyon<br>"
               . "&bull; <strong>Makipag-ugnayan</strong> — kung paano kami maabot<br>"
               . "&bull; <strong>Tungkol sa BFA</strong> — impormasyon ng asosasyon<br><br>"
               . "Maaari ka ring gumamit ng mga quick reply buttons sa ibaba.";
    } else {
        $reply = "Here's what I can help you with:<br><br>"
               . "&bull; <strong>Events / Activities</strong> — upcoming association events<br>"
               . "&bull; <strong>Announcements / News</strong> — latest updates<br>"
               . "&bull; <strong>Officers</strong> — current leadership<br>"
               . "&bull; <strong>Membership</strong> — how to join the association<br>"
               . "&bull; <strong>Contact</strong> — how to reach us<br>"
               . "&bull; <strong>About BFA</strong> — association information<br><br>"
               . "You can also use the quick reply buttons below.";
    }
    $type = 'html';

/* ── FALLBACK ── */
} else {
    if ($isTagalog) {
        $fallbacks = [
            "Hindi ko sigurado ang sagot doon, ngunit kaya kong tumulong sa mga tanong tungkol sa events, anunsyo, opisyal, at membership. Subukan ang quick reply buttons sa ibaba o i-type ang <strong>tulong</strong>.",
            "Magandang tanong! Mas magaling ako sa mga tanong tungkol sa aming events, opisyal, anunsyo, at kung paano sumali. I-type ang <strong>tulong</strong> para makita ang kaya ko.",
            "Maaaring wala akong sagot doon ngayon. Maaari kang bumisita sa aming <a href='contact_us.php' style='color:#1a56db;'>Contact page</a> para direktang makipag-ugnayan sa aming mga opisyal."
        ];
    } else {
        $fallbacks = [
            "I'm not sure about that, but I can help with questions about events, announcements, officers, and membership. Try the quick reply buttons below or type <strong>help</strong>.",
            "Great question! I'm best at answering questions about our events, officers, announcements, and how to join. Type <strong>help</strong> to see what I can do.",
            "I may not have an answer for that right now. You can visit our <a href='contact_us.php' style='color:#1a56db;'>Contact page</a> to reach our officers directly."
        ];
    }
    $reply = $fallbacks[array_rand($fallbacks)];
    $type  = 'html';
}

$response = ['reply' => $reply, 'type' => $type];
if ($action) $response['action'] = $action;

echo json_encode($response);
