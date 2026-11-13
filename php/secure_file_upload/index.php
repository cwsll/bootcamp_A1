
<?php

$maxSize = 5 * 1024 * 1024;

$allowedTypes = [
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
    "pdf"  => "application/pdf"
];

$message = "";
$status = "";
$checks = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_FILES["uploaded_file"])) {
        $message = "No file was selected.";
        $status = "error";
    } else {

        $file = $_FILES["uploaded_file"];
        $filename = $file["name"];
        $tmpPath = $file["tmp_name"];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($file["error"] !== UPLOAD_ERR_OK) {
            $message = "File upload failed. Please try again.";
            $status = "error";

        } else {

            // 1. Check file size
            if ($file["size"] > $maxSize || $file["size"] === 0) {
                $message = "File size is invalid. Maximum allowed size is 5 MB.";
                $status = "error";
            } else {
                $checks[] = "File size check passed.";

                // 2. Check file extension
                if (!array_key_exists($extension, $allowedTypes)) {
                    $message = "File extension is not allowed.";
                    $status = "error";
                } else {
                    $checks[] = "File extension check passed.";

                    // 3. Check actual MIME type
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($tmpPath);

                    if ($mimeType !== $allowedTypes[$extension]) {
                        $message = "File content does not match its extension.";
                        $status = "error";
                    } else {
                        $checks[] = "MIME type check passed.";

                        // 4. Generate a random secure filename
                        $newFilename = bin2hex(random_bytes(16)) . "." . $extension;

                        // 5. Move file to uploads directory
                        $uploadDirectory = __DIR__ . "/uploads/";

                        if (!is_dir($uploadDirectory)) {
                            $message = "Upload directory does not exist.";
                            $status = "error";
                        } elseif (!is_writable($uploadDirectory)) {
                            $message = "Upload directory is not writable.";
                            $status = "error";
                        } elseif (move_uploaded_file($tmpPath, $uploadDirectory . $newFilename)) {
                            $message = "File uploaded successfully and passed all security checks.";
                            $status = "success";
                            $checks[] = "Random filename generated.";
                            $checks[] = "File stored successfully.";
                        } else {
                            $message = "Could not save the uploaded file.";
                            $status = "error";
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure File Upload</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #101827;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #1e293b;
            padding: 35px;
            border-radius: 16px;
            border: 1px solid #334155;
        }

        h1 {
            text-align: center;
            color: #fb923c;
            margin-bottom: 12px;
        }

        .description {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .upload-box {
            border: 2px dashed #475569;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="file"] {
            width: 100%;
            margin-top: 20px;
            color: #cbd5e1;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #f97316;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #ea580c;
        }

        .result {
            margin-top: 22px;
            padding: 18px;
            border-radius: 10px;
            line-height: 1.8;
        }

        .success {
            background: #064e3b;
            border: 1px solid #10b981;
        }

        .error {
            background: #7f1d1d;
            border: 1px solid #ef4444;
        }

        .checks {
            margin-top: 12px;
            color: #bbf7d0;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            color: #64748b;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Secure File Upload</h1>

    <p class="description">
        Upload your file and let our security system
        verify it through multiple protection layers.
    </p>

    <form method="POST" enctype="multipart/form-data">

        <div class="upload-box">
            <h3>Select Your File</h3>
            <p style="color:#94a3b8; margin-top:10px;">
                Allowed formats: JPG, JPEG, PNG, PDF
            </p>
            <p style="color:#94a3b8; margin-top:5px;">
                Maximum file size: 5 MB
            </p>

            <input
                type="file"
                name="uploaded_file"
                accept=".jpg,.jpeg,.png,.pdf"
                required
            >
        </div>

        <button type="submit">Upload & Scan File</button>

    </form>

    <?php if ($message !== ""): ?>

        <div class="result <?php echo $status; ?>">

            <strong>
                <?php echo htmlspecialchars($message); ?>
            </strong>

            <?php if (!empty($checks)): ?>
                <div class="checks">
                    <?php foreach ($checks as $check): ?>
                        <div>
                            ✓ <?php echo htmlspecialchars($check); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

    <?php endif; ?>

    <div class="footer">
        Secure File Upload System | PHP Security Assignment 12
    </div>

</div>

</body>
</html>
