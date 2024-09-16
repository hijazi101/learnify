<?php
session_start();
include("data.php");
$username = $_SESSION['username'];

if (isset($_POST["submit"]) && isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
    $file_name = $_FILES["image"]["name"];
    $file_temp = $_FILES["image"]["tmp_name"];
    $file_size = $_FILES["image"]["size"];
    $file_type = $_FILES["image"]["type"];

    $upload_dir = "uploads/";
    $file_path = $upload_dir . uniqid() . '_' . $file_name;

    if (move_uploaded_file($file_temp, $file_path)) {
        $price = $_POST["price"];
        $coins = $_POST["coins"];
        $sql = "INSERT INTO balance (name, image, price, coins) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $file_name, $file_path, $price, $coins);
        if ($stmt->execute()) {
            echo "File uploaded successfully";
            header("location: balance.php");
            exit();
        } else {
            echo "Error uploading file: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading file. Please try again.";
    }
}

$sql = "SELECT id, image, price, coins FROM balance";
$result = $conn->query($sql);

$images = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
} else {
    echo "<p class='no-images'>No images found</p>";
}

if (isset($_POST["buy"])) {
    $imageId = $_POST["image_id"];
    $sql_buy = "UPDATE user u 
                JOIN balance b ON b.id = ? 
                SET u.amount = u.amount + b.coins 
                WHERE u.username = ?";
    $stmt_buy = $conn->prepare($sql_buy);
    $stmt_buy->bind_param("is", $imageId, $username);
    if ($stmt_buy->execute()) {
        echo "Purchase successful";
        header("location: balance.php");
        exit();
    } else {
        echo "Error processing purchase: " . $conn->error;
    }
    $stmt_buy->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance</title>
    <link rel="stylesheet" href="balance.css">
</head>
<body>
    <div class="header">
        <?php include("header.php"); ?>
    </div>
    <?php if ($username == 'hijazi') { ?>
        <form action="balance.php" method="post" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="image" required class="form-input">
            <input type="text" name="price" id="price" placeholder="Enter price" required class="form-input">
            <input type="text" name="coins" id="coins" placeholder="Enter coins" required class="form-input">
            <input type="submit" name="submit" value="Upload" class="form-button">
        </form>
    <?php } ?>
    <div class="gallery">
        <?php foreach ($images as $index => $image) {
            if ($index % 3 == 0) echo '<div class="row">';
            ?>
            <div class="image-container">
                <img src="<?php echo $image['image']; ?>" alt="Uploaded Image" class="uploaded-image">
                <div class="image-details">
                    <p>Price: <?php echo htmlspecialchars($image['price']); ?></p>
                    <p>Coins: <?php echo htmlspecialchars($image['coins']); ?></p>
                    <form action="balance.php" method="post">
                        <input type="hidden" name="image_id" value="<?php echo htmlspecialchars($image['id']); ?>">
                        <input type="submit" name="buy" value="Buy" class="buy-button">
                    </form>
                </div>
            </div>
            <?php
            if ($index % 3 == 2) echo '</div>';
        }
        if (count($images) % 3 != 0) echo '</div>'; // Close the last row if it's not complete
        ?>
    </div>
</body>
</html>
