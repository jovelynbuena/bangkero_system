# 🛡️ DEFENSE DAY BACKUP SETUP GUIDE

## Purpose
Mag-setup ng localhost version ng system para gumana kahit:
- ❌ Walang internet
- ❌ Hindi accessible ang freesqldatabase.com
- ❌ May problema sa remote database

---

## 📋 STEP-BY-STEP GUIDE

### **STEP 1: Create Localhost Database** (5 minutes)

1. **Open phpMyAdmin:**
   ```
   http://localhost/phpmyadmin
   ```

2. **Create new database:**
   - Click "New" sa left panel
   - Database name: `bangkero_local`
   - Collation: `utf8_general_ci`
   - Click "Create"

---

### **STEP 2: Backup Current Database** (2 minutes)

1. **Go to Backup page:**
   ```
   http://localhost/bangkero_system/index/utilities/backup.php
   ```

2. **Create backup:**
   - Click "Create Backup Now"
   - Download the `.sql` file
   - Save to: `c:/xampp/htdocs/bangkero_system/config/backup_latest.sql`

---

### **STEP 3: Import to Localhost** (3 minutes)

**Option A: Using phpMyAdmin (EASY)**
1. Go to: `http://localhost/phpmyadmin`
2. Click `bangkero_local` database (left panel)
3. Click "Import" tab (top)
4. Click "Choose File" → select your `.sql` backup
5. Scroll down → Click "Import"
6. Wait... ✅ Done!

**Option B: Using Restore feature (EASIER)**
1. First, create `config/db_connect_local.php` (see Step 4)
2. Edit `restore_action.php` to use local connection
3. Go to backup page → Upload SQL file → Restore
4. ✅ Done!

---

### **STEP 4: Create Local Config File** (1 minute)

Create file: `config/db_connect_local.php`
```php
<?php
$servername = "localhost";
$username   = "root";
$password   = "";  // blank for XAMPP default
$dbname     = "bangkero_local";
$port       = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Local database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// For activity logs (optional)
$connLog = new mysqli($servername, $username, $password, $dbname, $port);
?>
```

---

### **STEP 5: Create Switch Script** (AUTOMATIC)

We'll create a file that easily switches between:
- 🌐 **ONLINE mode** → freesqldatabase.com
- 💻 **OFFLINE mode** → localhost

File: `config/switch_mode.php`

---

### **STEP 6: Testing** (2 minutes)

1. **Test localhost connection:**
   ```
   Visit: http://localhost/bangkero_system/config/test_local.php
   ```

2. **Try login with local database**

3. **Verify all features work**

---

## 🎓 DEFENSE DAY PROCEDURE

### **Night Before Defense:**
```
1. ✅ Create fresh backup from online database
2. ✅ Import to bangkero_local
3. ✅ Test localhost version
4. ✅ Save backup to USB + Google Drive
5. ✅ Test on laptop you'll use for defense
```

### **Defense Day:**

**Option 1: If internet is stable**
```
→ Use ONLINE mode (current setup)
→ Keep localhost ready as backup
```

**Option 2: If internet is problematic**
```
→ Switch to OFFLINE mode
→ Everything runs from localhost
→ 100% guaranteed to work!
```

---

## 🔄 HOW TO SWITCH MODES

### Switch to OFFLINE (localhost):
```php
// Edit: config/db_connect.php
// Change line 1:
require_once('db_connect_local.php');  // Use this
// require_once('db_connect_online.php'); // Comment this
```

### Switch to ONLINE:
```php
// Edit: config/db_connect.php
require_once('db_connect_online.php');  // Use this
// require_once('db_connect_local.php'); // Comment this
```

---

## ⚠️ IMPORTANT NOTES

✅ **Uploads folder:**
   - Images are stored in `uploads/` folder (hindi sa database)
   - Copy entire `uploads/` folder kasama sa backup

✅ **Sessions:**
   - May need mag-login ulit pag nag-switch ng mode

✅ **Testing:**
   - Test BOTH modes 1 week before defense
   - Practice switching between modes

✅ **Backup everything:**
   - Database SQL file
   - Entire project folder
   - Uploads folder
   - USB + Cloud backup

---

## 📂 BACKUP CHECKLIST

```
□ Latest SQL backup file
□ config/db_connect_local.php created
□ bangkero_local database created & imported
□ Test localhost login works
□ Test all features work offline
□ Copy to USB drive
□ Upload to Google Drive
□ Test on defense laptop
```

---

## 🆘 TROUBLESHOOTING

**Problem: "Table doesn't exist" after import**
```
→ Solution: Import SQL file again
→ Make sure you selected correct database
```

**Problem: "Access denied for user 'root'"**
```
→ Solution: Check XAMPP is running
→ Start MySQL service
```

**Problem: "Cannot connect to localhost"**
```
→ Solution: Check XAMPP Apache & MySQL are running
→ Green checkmarks in XAMPP Control Panel
```

---

**Created by: CodeBuddy Assistant**
**Date: February 9, 2025**
**For: Bangkero System Defense Preparation**
