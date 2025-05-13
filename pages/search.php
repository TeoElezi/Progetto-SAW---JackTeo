<?php
    include '../includes/header.php'; // Inclusione dell'header

    if (isset($_GET['q'])) {
        $search = mysqli_real_escape_string($conn, $_GET['q']);

        $query = "SELECT * FROM news WHERE title LIKE '%$search%' OR content LIKE '%$search%' ORDER BY posted_at DESC";
        $result = mysqli_query($conn, $query);

        

        echo "<h2>Risultati per: <em>" . htmlspecialchars($search) . "</em></h2>";

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<div class="col">';
                echo '  <div class="card h-100">';
                if ($row['image_url']) {
                    echo '    <img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['image_alt']) . '">';
                }
                echo '    <div class="card-body">';
                echo '      <h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';
                echo '      <p class="card-text">' . substr(strip_tags($row['content']), 0, 120) . '...</p>';
                echo '      <a href="'. htmlspecialchars($row['link']) .'" class="btn btn-primary">Leggi</a>';
                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo "<p>Nessun risultato trovato.</p>";
        }
    }
include '../includes/footer.php'; // Inclusione del footer
?>