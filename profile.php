<?php
session_start();
include("data.php");

// Custom error handler to convert warnings to exceptions
function warning_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// Set the custom error handler
set_error_handler("warning_handler");

try {
    // Profile picture upload
    if (isset($_POST["submit"])) {
        if (!empty($_FILES["imageUpload"]["name"])) {
            $username = $_SESSION['username'];
            $target_directory = "uploads/";
            $target_file = $target_directory . basename($_FILES["imageUpload"]["name"]);

            if (move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $target_file)) {
                // Check if profile picture already exists
                $profile_query = "SELECT profile_picture FROM profile WHERE username = '$username'";
                $profile_result = mysqli_query($conn, $profile_query);

                if ($profile_result && $profile_result->num_rows > 0) {
                    // If profile picture exists, update it
                    $img_sql = "UPDATE profile SET profile_picture = '$target_file' WHERE username = '$username'";
                    $img_result = mysqli_query($conn, $img_sql);

                    if ($img_result) {
                        echo "Profile picture updated successfully.";
                    } else {
                        echo "Error: Unable to update profile picture.";
                    }
                } else {
                    // If profile picture doesn't exist, insert it
                    $img_sql = "INSERT INTO profile (username, profile_picture) VALUES ('$username', '$target_file')";
                    $img_result = mysqli_query($conn, $img_sql);

                    if ($img_result) {
                        echo "Profile picture inserted successfully.";
                    } else {
                        echo "Error: Unable to insert profile picture.";
                    }
                }
            } else {
                echo "Error: There was a problem uploading your file.";
            }
        } else {
            echo "Please select an image.";
        }
    }
} catch (ErrorException $e) {
    // Handle the warning as an exception
    echo "Caught an exception: ", $e->getMessage(), "\n";
    echo "In file: ", $e->getFile(), " on line ", $e->getLine(), "\n";
}

// Bio update
if (isset($_POST["update_bio"])) {
    $username = $_SESSION['username'];
    $bio = $_POST["bio"];
    $bio_query = "SELECT * FROM profile WHERE username = '$username'";
    $bio_result = mysqli_query($conn, $bio_query);

    if ($bio_result && $bio_result->num_rows > 0) {
        // If bio exists, update it
        $bio_sql = "UPDATE profile SET bio = '$bio' WHERE username = '$username'";
        $bio_result = mysqli_query($conn, $bio_sql);

        if ($bio_result) {
            echo "Bio updated successfully.";
        } else {
            echo "Error: Unable to update bio.";
        }
    } else {
        // If bio doesn't exist, insert it
        $bio_sql = "INSERT INTO profile (username, bio) VALUES ('$username', '$bio')";
        $bio_result = mysqli_query($conn, $bio_sql);

        if ($bio_result) {
            echo "Bio inserted successfully.";
        } else {
            echo "Error: Unable to insert bio.";
        }
    }
}

// Fetch profile picture and bio
$username = $_SESSION['username'];
$profile_query = "SELECT profile_picture, bio FROM profile WHERE username = '$username'";
$profile_result = mysqli_query($conn, $profile_query);
$profile_data = mysqli_fetch_assoc($profile_result);

$profile_picture = isset($profile_data['profile_picture']) ? $profile_data['profile_picture'] : 'default.jpg';
$bio_data = isset($profile_data['bio']) ? $profile_data['bio'] : 'No bio available';

// Function to retrieve user's requests
function getreq() {
    include("data.php");
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM requsets WHERE username='$username' ORDER BY req_date DESC";
    $result = $conn->query($sql);

    $req = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $req[] = $row;
        }
    }

    $result->close();
    $conn->close();

    return $req;
}

$reqs = getreq(); // Call the function once

// Function to retrieve user's schedule
function getsecdual() {
    include("data.php");
    $username = $_SESSION['username'];
    $currentDate = date("Y-m-d");
    $sql = "SELECT * FROM lessons WHERE username='$username' AND date >= '$currentDate' ORDER BY date";
    $result = $conn->query($sql);

    $scedual = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $scedual[] = $row;
        }
    }

    $result->close();
    $conn->close();

    return $scedual;
}

$scedual = getsecdual();

restore_error_handler();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/608545112e.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="profile.css">
  <title>Profile</title>
</head>
<body>
<?php include("header.php"); ?>
<div class="profile">
  <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
</div>
<div class="bio">
  <p>Bio: <?php echo htmlspecialchars($bio_data); ?></p>
</div>

<div id="editProfileSection" class="profile-section" style="display: none;">
  <form action="profile.php" method="post" enctype="multipart/form-data" class="bio-form">
    <!-- Change Bio form -->
    <br>
    <label for="bio" class="form-label">Change Bio:</label>
    <input type="text" id="bio" name="bio" value="<?php echo htmlspecialchars($bio_data); ?>" class="bio-input">
    <input type="submit" name="update_bio" value="Change Bio" class="edit-profile-btn">
  </form>

  <!-- Upload Image form -->
  <form action="profile.php" method="post" enctype="multipart/form-data" class="profile-pic-form">
    <input type="hidden" name="MAX_FILE_SIZE" value="500000">
    <label for="imageUpload" class="form-label">Choose a new profile picture:</label>
    <input type="file" id="imageUpload" name="imageUpload" class="file-input">
    <input type="submit" name="submit" value="Upload Image" class="edit-profile-btn">
  </form>
</div>

<div class="edit">
  <button id="editProfileBtn" class="edit-profile-btn">Edit Profile</button>
</div>
<main>
  <section class="request-section">
    <?php foreach ($reqs as $req) : ?>
      <div class="req-box">
        <div class="req-content">
          <p><strong><?php echo htmlspecialchars($req['username']); ?>:</strong></p>
          <p><?php echo htmlspecialchars($req['requset']); ?></p>
        </div>

        <div class="resp-section">
          <!-- Response form -->
          <form class="resp-form" action="home.php" method="post">
            <input type="hidden" name="rsp_id" value="<?php echo htmlspecialchars($req['id']); ?>">
            <input type="text" name="response" placeholder="Write a response..." class="response-input">
            <button type="submit" name="responses" class="response-btn"><i class="fa-regular fa-comment"></i></button>
          </form>

          <div class="responses">
            <?php 
            $req_query = "SELECT * FROM respons WHERE req_id = '{$req['id']}'";
            $req_result = mysqli_query($conn, $req_query);
            $responses = [];
            if ($req_result) {
              while ($response = mysqli_fetch_assoc($req_result)) {
                $responses[] = $response;
              }
            } else {
              echo "Error retrieving responses: " . mysqli_error($conn);
            }
            foreach ($responses as $response) : ?>
              <div class="response">
                <p><strong><?php echo htmlspecialchars($response['username']); ?>:</strong> <?php echo htmlspecialchars($response['response']); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Timestamp -->
        <div class="timestamp">
          <p>Posted: <?php echo htmlspecialchars($req['req_date']); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
</main>

<div class="schedual">
  <section class="schedual-section">
    <h1>Schedule</h1>
    <?php foreach ($scedual as $lesson) : ?>
      <div class="schedual-box">
        <div class="schedual-content">
          <p><strong><?php echo htmlspecialchars($lesson['date']); ?>:</strong></p>
          <p><?php echo htmlspecialchars($lesson['name']); ?></p>
          <p><?php echo "Number of students: ", htmlspecialchars($lesson['isbooked']); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<script>
document.getElementById('editProfileBtn').addEventListener('click', function() {
    const editProfileSection = document.getElementById('editProfileSection');
    editProfileSection.style.display = editProfileSection.style.display === 'none' ? 'block' : 'none';
});
</script>
</body>
</html>
