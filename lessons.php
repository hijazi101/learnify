<?php
include("data.php");
session_start();
$username = $_SESSION['username'];

//INPUT SESSION
if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $date = $_POST["date"];
    $coins = $_POST["coins"];

    $sql = "INSERT INTO lessons (username, name, date, coins) VALUES ('$username', '$name', '$date', '$coins')";
    $result = $conn->query($sql);
    if ($result) {
        echo "success";
        header("location: lessons.php");
    } else {
        echo "failed";
    }
}

//BOOK A SESSION
function getlessons($searchTerm = "")
{
    global $conn; // Use the global $conn variable
    $currentDate = date("Y-m-d");
    $sql = "SELECT * FROM lessons WHERE date >= '$currentDate'";
    if ($searchTerm != "") {
        $sql .= " AND name LIKE '%$searchTerm%'";
    }
    $sql .= " ORDER BY date DESC";

    $result = $conn->query($sql);
    $lessons = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $lessons[] = $row;
        }
    }
    $result->close();
    return $lessons;
}
//Book button
$phonesql = "SELECT PHONE FROM USER WHERE username = '$username'";
$phoneresult = $conn->query($phonesql);
$phoneRow = $phoneresult->fetch_assoc();
$phonenb = $phoneRow['PHONE']; // Extract phone number from fetched row

//BOOK A SESSION
if (isset($_POST["book"])) {
    $lessonId = $_POST["lessonId"];
    $sql = "UPDATE lessons SET isbooked = isbooked + 1 WHERE id = '$lessonId'";
    $result = $conn->query($sql);
    if ($result) {
        echo "BOOKED SUCCESSFULLY";
        header("location: lessons.php");
    } else {
        echo "Failed to book the lesson.";
    }
}

if (isset($_POST["unbook"])) {
    $lessonId = $_POST["lessonId"];
    $sql = "UPDATE LESSONS SET ISBOOKED = 0, BOOKEDBY = '', PHONENB = 0 WHERE id = '$lessonId'";
    $result = $conn->query($sql);
    if ($result) {
        echo "UNBOOKED SUCCESSFULLY";
        header("location: lessons.php");
    } else {
        echo "failed";
    }
}

//search
if (isset($_GET['q'])) {
    $searchTerm = $_GET['q'];
    $lessons = getlessons($searchTerm);
    echo json_encode($lessons);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lessons.css">
    <title>Document</title>
</head>
<body>
    <div class="header">
        <?php include("header.php"); ?>
    </div>

    <div class="title">
        <p>Take Private Lessons</p>
    </div>

    <div class="form-container">
        <form action="lessons.php" method="post" enctype="multipart/form-data" class="lesson-form">
            <input type="text" name="name" placeholder="Lesson Name"><br>
            <input type="datetime-local" name="date"><br>
            <input type="text" name="coins" placeholder="Price"><br>
            <input type="submit" name="submit" value="Submit">
        </form>
    </div>

    <div id="search-container">
        <input type="text" id="search-input" placeholder="Enter lesson name">
        <button id="search-button"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></button>
    </div>

    <div id="search-results" class="lessons-container">
        <?php
        $lessons = getlessons();
        foreach ($lessons as $lesson) {
            $lessonId = $lesson['id'];
            $lessonUsername = $lesson['username'];
            $name = $lesson['name'];
            $date = $lesson['date'];
        ?>
        <div class="lesson">
            <p><strong><?php echo $lessonUsername; ?>:</strong></p>
            <p><?php echo $name; ?></p>
            <p><?php echo $date; ?></p>
            <form action="lessons.php" method="post">
                <input type="hidden" name="lessonId" value="<?php echo $lessonId; ?>">
                <input type="submit" name="book" value="Book">
                <input type="submit" name="unbook" value="Unbook">
            </form>
        </div>
        <?php } ?>
    </div>

    <script>
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    const searchResults = document.getElementById('search-results');

    searchButton.addEventListener('click', () => {
        const searchTerm = searchInput.value;

        if (!searchTerm) {
            // Reload all lessons if search term is empty
            location.reload();
            return;
        }

        fetch(`lessons.php?q=${searchTerm}`)
            .then(response => response.json())
            .then(results => {
                searchResults.innerHTML = ''; // Clear results container
                displaySearchResults(results); // Call function to display results
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                // Handle errors (optional)
            });
    });

    function displaySearchResults(results) {
        let resultsHtml = '';
        for (const result of results) {
            resultsHtml += `
                <div class="lesson">
                    <p><strong>${result.username}:</strong></p>
                    <p>${result.name}</p>
                    <p>${result.date}</p>
                    <form action="lessons.php" method="post">
                        <input type="hidden" name="lessonId" value="${result.id}">
                        <input type="submit" name="book" value="Book">
                        <input type="submit" name="unbook" value="Unbook">
                    </form>
                </div>
            `;
        }
        searchResults.innerHTML = resultsHtml;
    }
    </script>
</body>
</html>

<?php
$conn->close();
?>