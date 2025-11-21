<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../../config/db_connect.php');

$errors = [];
$success = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    
    if (empty($title)) {
        $errors[] = "Gallery title is required.";
    }

    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = "../uploads/gallery/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedFiles = [];
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = time() . "_" . basename($_FILES['images']['name'][$key]);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $targetPath)) {
                $uploadedFiles[] = $fileName;
            }
        }

        if (!empty($uploadedFiles)) {
            $images = implode(",", $uploadedFiles);

            $sql = "INSERT INTO galleries (title, images) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $title, $images);
            
            if ($stmt->execute()) {
                $success = "✅ Gallery created successfully!";
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
        }
    } else {
        $errors[] = "Please upload at least one image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
        }
        .content {
            max-width: 900px;
            margin: 80px auto; /* pushes below navbar */
            padding: 20px;
        }
        .form-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #01579b;
        }
        .btn-success {
            background-color: #ff7043;
            border-color: #ff7043;
        }
        .btn-success:hover {
            background-color: #00897b;
            border-color: #00897b;
        }
        .alert-success {
            background: #e0f7fa;
            color: #00897b;
            border-radius: 8px;
        }
        .alert-danger {
            background: #ffebee;
            color: #d32f2f;
            border-radius: 8px;
        }
        .upload-box {
            border: 2px dashed #ccc;
            padding: 40px;
            text-align: center;
            color: #777;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .upload-box:hover {
            background: #f1f1f1;
        }
        #preview img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="content">
    <h2 class="mb-4">➕ Add New Gallery</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-section">
      <div class="mb-3">
        <label for="title" class="form-label">Gallery Title</label>
        <input type="text" name="title" id="title" class="form-control" placeholder="Enter gallery title">
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Images</label>
        <div class="upload-box" onclick="document.getElementById('images').click();">
          Drop files here or <span class="text-primary">Select Files</span>
        </div>
        <input type="file" name="images[]" id="images" class="d-none" multiple>

        <!-- Preview Section -->
        <div id="preview" class="mt-3 d-flex flex-wrap gap-3"></div>
      </div>

      <button type="submit" class="btn btn-success">Publish</button>
      <button type="reset" class="btn btn-secondary">Save Draft</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('images').addEventListener('change', function(event) {
    let preview = document.getElementById('preview');
    preview.innerHTML = ""; // Clear old previews
    
    Array.from(event.target.files).forEach(file => {
        if (file.type.startsWith("image/")) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("img-thumbnail");
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>

</body>
</html>
