<?php
session_start();
include("data.php");

// Define the getrequests function before any code that uses it
function getrequests($searchTerm = "") {
    global $conn;
    $sql = "SELECT * FROM requsets";
    if (!empty($searchTerm)) {
        $sql .= " WHERE requset LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
    }
    $sql .= " ORDER BY req_date DESC";
    $result = $conn->query($sql);
    $requests = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    $result->close();
    return $requests;
}

if (isset($_POST["logout"])) {
    session_destroy();
    header("location:login.php");
    exit();
}

if (isset($_POST["submit"])) {
    $req = $_POST["request"];
    if (empty($req)) {
        echo "Enter a request first";
    } else {
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            $user_id_query = "SELECT id FROM user WHERE username='$username'";
            $user_id_result = mysqli_query($conn, $user_id_query);
            if ($user_id_row = mysqli_fetch_assoc($user_id_result)) {
                $user_id = $user_id_row['id'];
            }
            mysqli_free_result($user_id_result);
            $sql = "INSERT INTO requsets (requset, username) VALUES ('$req', '$username')";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                header("location: index.php");
            } else {
                echo "Request failed";
            }
        }
    }
}

if (isset($_POST["response"])) {
    $reqId = $_POST['rsp_id'];
    $respText = mysqli_real_escape_string($conn, $_POST['response']);
    if (empty($respText)) {
        echo "Enter a response first";
    } else {
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            $insertRespQuery = "INSERT INTO respons (response, username, req_id) VALUES ('$respText', '$username', '$reqId')";
            $insertRespResult = mysqli_query($conn, $insertRespQuery);
            if ($insertRespResult) {
                header("location: index.php");
                exit();
            } else {
                echo "Failed to add response";
            }
        }
    }
}

if (isset($_GET['q'])) {
    $searchTerm = $_GET['q'];
    $requests = getrequests($searchTerm);
    echo json_encode($requests);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/608545112e.js" crossorigin="anonymous"></script>
    <title>learnify</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <div class="header">
        <?php include("header.php"); ?>
    </div>
    <div class="my-logout-button">
        <form action="index.php" method="post">
            <input type="submit" value="Logout" name="logout">
        </form>
    </div>
    <main>
        <section class="request-section">
            <form action="index.php" method="post">
                <textarea name="request" id="request-box" placeholder="Enter your request"></textarea>
                <input type="submit" value="Ask" name="submit" id="ask-btn">
            </form>
        </section>
        <div id="search-container">
            <input type="text" id="search-input" placeholder="Enter request">
            <button id="search-button"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></button>
        </div>
        <section class="request-section" id="requests-section">
            <?php
            $requests = getrequests();
            foreach ($requests as $request) { 
                $username = $request['username'];
                $reqContent = $request['requset'];
                $date = $request['req_date'];
                $reqId = $request['id'];
                ?>
                <div class="req-box">
                    <div class="req-content">
                        <p><strong><?php echo $username; ?>:</strong></p>
                        <p><?php echo $reqContent; ?></p>
                    </div>
                    <div class="resp-section">
                        <form class="resp-form" action="index.php" method="post">
                            <input type="hidden" name="rsp_id" value="<?php echo $reqId; ?>">
                            <input type="text" name="response" placeholder="Write a response...">
                            <button type="submit" name="responses"><i class="fa-regular fa-comment"></i></button>
                        </form>
                        <div class="responses">
                            <?php 
                            $resp_query = "SELECT * FROM respons WHERE req_id = '$reqId'";
                            $resp_result = mysqli_query($conn, $resp_query);
                            $responses = [];
                            if ($resp_result) {
                                while ($response = mysqli_fetch_assoc($resp_result)) {
                                    $responses[] = $response;
                                }
                            } else {
                                echo "Error retrieving responses: " . mysqli_error($conn);
                            }
                            foreach ($responses as $response) : ?>
                                <div class="response">
                                    <p><strong><?php echo $response['username']; ?>:</strong> <?php echo $response['response']; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="timestamp">
                        <p>Posted: <?php echo $date; ?></p>
                    </div>
                </div>
            <?php } ?>
        </section>
    </main>
    <script>
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');
        const requestsSection = document.getElementById('requests-section');

        searchButton.addEventListener('click', () => {
            const searchTerm = searchInput.value;

            fetch(`index.php?q=${searchTerm}`)
                .then(response => response.json())
                .then(results => {
                    requestsSection.innerHTML = ''; // Clear results container
                    displaySearchResults(results); // Call function to display results
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                });
        });

        searchInput.addEventListener('input', () => {
            if (searchInput.value === "") {
                // Reload all requests if search term is empty
                fetch(`index.php?q=`)
                    .then(response => response.json())
                    .then(results => {
                        requestsSection.innerHTML = '';
                        displaySearchResults(results);
                    })
                    .catch(error => {
                        console.error('Error fetching all requests:', error);
                    });
            }
        });

        function displaySearchResults(results) {
            let resultsHtml = '';
            for (const result of results) {
                resultsHtml += `
                    <div class="req-box">
                        <div class="req-content">
                            <p><strong>${result.username}:</strong></p>
                            <p>${result.requset}</p>
                        </div>
                        <div class="resp-section">
                            <form class="resp-form" action="index.php" method="post">
                                <input type="hidden" name="rsp_id" value="${result.id}">
                                <input type="text" name="response" placeholder="Write a response...">
                                <button type="submit" name="responses"><i class="fa-regular fa-comment"></i></button>
                            </form>
                            <div class="responses">
                                ${result.responses ? result.responses.map(response => `
                                    <div class="response">
                                        <p><strong>${response.username}:</strong> ${response.response}</p>
                                    </div>
                                `).join('') : ''}
                            </div>
                        </div>
                        <div class="timestamp">
                            <p>Posted: ${result.req_date}</p>
                        </div>
                    </div>
                `;
            }
            requestsSection.innerHTML = resultsHtml;
        }
    </script>
</body>
</html>
