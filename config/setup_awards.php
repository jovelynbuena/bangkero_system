<?php
/**
 * Awards Table Setup & Sample Data
 * Run this file once to create the awards table and populate sample data
 */

require_once('db_connect.php');

// Create awards table
$createTableQuery = "CREATE TABLE IF NOT EXISTS awards (
    award_id INT PRIMARY KEY AUTO_INCREMENT,
    award_title VARCHAR(255) NOT NULL,
    awarding_body VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    year_received INT,
    date_received DATE,
    award_image VARCHAR(255),
    certificate_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_year (year_received),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($createTableQuery)) {
    echo "✓ Awards table created successfully<br>";
} else {
    echo "✗ Error creating table: " . $conn->error . "<br>";
}

// Check if sample data already exists
$checkQuery = "SELECT COUNT(*) as count FROM awards";
$result = $conn->query($checkQuery);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert sample awards data
    $sampleAwards = [
        [
            'title' => 'Outstanding Community Service Award',
            'body' => 'City Government of Olongapo',
            'category' => 'Local',
            'description' => 'Recognized for exceptional dedication to community service and unwavering commitment to supporting local fishermen and their families through various livelihood programs and sustainability initiatives.',
            'year' => 2025,
            'date' => '2025-01-15'
        ],
        [
            'title' => 'Best Fisherfolk Association',
            'body' => 'Bureau of Fisheries and Aquatic Resources (BFAR) Region 3',
            'category' => 'Regional',
            'description' => 'Awarded for exemplary performance in promoting sustainable fishing practices, environmental conservation, and active participation in regional fisheries development programs.',
            'year' => 2024,
            'date' => '2024-11-20'
        ],
        [
            'title' => 'Environmental Excellence Award',
            'body' => 'Department of Environment and Natural Resources (DENR)',
            'category' => 'National',
            'description' => 'In recognition of outstanding efforts in marine conservation, coastal cleanup initiatives, and advocacy for sustainable fishing methods that protect aquatic ecosystems.',
            'year' => 2024,
            'date' => '2024-08-12'
        ],
        [
            'title' => 'Safety Champion Award',
            'body' => 'Philippine Coast Guard',
            'category' => 'National',
            'description' => 'Commended for maintaining excellent safety standards, conducting regular safety training programs, and promoting maritime safety awareness among fishermen.',
            'year' => 2023,
            'date' => '2023-12-05'
        ],
        [
            'title' => 'Livelihood Innovation Award',
            'body' => 'Department of Labor and Employment (DOLE)',
            'category' => 'Regional',
            'description' => 'Recognized for innovative livelihood programs that provided alternative income sources for fisherfolks during closed fishing seasons and enhanced economic resilience.',
            'year' => 2023,
            'date' => '2023-09-18'
        ],
        [
            'title' => 'Community Partnership Award',
            'body' => 'Subic Bay Metropolitan Authority (SBMA)',
            'category' => 'Local',
            'description' => 'Awarded for fostering strong partnerships with local government units, private sectors, and NGOs to advance the welfare of the fishing community.',
            'year' => 2023,
            'date' => '2023-06-22'
        ],
        [
            'title' => 'Youth Development Excellence',
            'body' => 'National Youth Commission',
            'category' => 'National',
            'description' => 'In recognition of exemplary youth programs that engaged young fishermen in skills training, leadership development, and sustainable fishing education.',
            'year' => 2022,
            'date' => '2022-10-30'
        ],
        [
            'title' => 'Best Coastal Resource Management',
            'body' => 'League of Municipalities Philippines',
            'category' => 'Regional',
            'description' => 'Commended for effective coastal resource management practices, including mangrove reforestation, coral reef protection, and fish sanctuary establishment.',
            'year' => 2022,
            'date' => '2022-07-14'
        ],
        [
            'title' => 'Food Security Contributor Award',
            'body' => 'National Food Authority',
            'category' => 'National',
            'description' => 'Recognized for significant contributions to national food security through sustainable fish production and distribution programs.',
            'year' => 2022,
            'date' => '2022-04-08'
        ],
        [
            'title' => 'Disaster Resilience Award',
            'body' => 'National Disaster Risk Reduction and Management Council',
            'category' => 'Regional',
            'description' => 'Awarded for implementing effective disaster preparedness programs and demonstrating exceptional community resilience during natural calamities.',
            'year' => 2021,
            'date' => '2021-11-25'
        ],
        [
            'title' => 'Gender Equality Champion',
            'body' => 'Philippine Commission on Women',
            'category' => 'National',
            'description' => 'Recognized for promoting gender equality in the fishing industry and empowering women fishermen through inclusive programs and leadership opportunities.',
            'year' => 2021,
            'date' => '2021-08-19'
        ],
        [
            'title' => 'Health and Wellness Award',
            'body' => 'Department of Health Region 3',
            'category' => 'Regional',
            'description' => 'Commended for implementing comprehensive health programs, medical missions, and wellness initiatives that improved the overall health of the fishing community.',
            'year' => 2021,
            'date' => '2021-05-10'
        ]
    ];

    $insertQuery = "INSERT INTO awards (award_title, awarding_body, category, description, year_received, date_received) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    $successCount = 0;
    foreach ($sampleAwards as $award) {
        $stmt->bind_param("ssssis", 
            $award['title'], 
            $award['body'], 
            $award['category'], 
            $award['description'], 
            $award['year'], 
            $award['date']
        );
        
        if ($stmt->execute()) {
            $successCount++;
        } else {
            echo "✗ Error inserting award: " . $stmt->error . "<br>";
        }
    }

    echo "✓ Successfully inserted {$successCount} sample awards<br>";
    $stmt->close();
} else {
    echo "ℹ Sample data already exists ({$row['count']} awards found)<br>";
}

// Create awards directory if it doesn't exist
$awardsDir = dirname(__DIR__) . '/uploads/awards';
if (!file_exists($awardsDir)) {
    if (mkdir($awardsDir, 0755, true)) {
        echo "✓ Created awards upload directory<br>";
    } else {
        echo "✗ Failed to create awards directory<br>";
    }
} else {
    echo "ℹ Awards directory already exists<br>";
}

echo "<br><strong>Setup completed!</strong><br>";
echo "<a href='../index/home/awards.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 8px;'>View Awards Page</a>";

$conn->close();
?>
