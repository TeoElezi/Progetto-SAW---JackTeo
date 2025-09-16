<?php
    include '../includes/header.php';

    if (isset($_GET['q'])) {
        $search = trim($_GET['q']);
        if (strlen($search) > 255) { $search = substr($search, 0, 255); }
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM news WHERE title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')");
        $stmtCount->bind_param('ss', $search, $search);
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
        $stmtCount->close();

        $stmt = $conn->prepare("SELECT * FROM news WHERE title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%') ORDER BY posted_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ssii', $search, $search, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

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

            $totalPages = (int)ceil($total / $perPage);
            if ($totalPages > 1) {
                echo '<nav aria-label="Risultati">';
                echo '<ul class="pagination justify-content-center mt-4">';
                if ($page > 1) {
                    $p = $page - 1;
                    echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($search) . '&page=' . $p . '">Precedente</a></li>';
                }
                for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++) {
                    $active = $i === $page ? ' active' : '';
                    echo '<li class="page-item' . $active . '"><a class="page-link" href="?q=' . urlencode($search) . '&page=' . $i . '">' . $i . '</a></li>';
                }
                if ($page < $totalPages) {
                    $n = $page + 1;
                    echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($search) . '&page=' . $n . '">Successiva</a></li>';
                }
                echo '</ul>';
                echo '</nav>';
            }
        } else {
            echo "<p>Nessun risultato trovato.</p>";
        }
        $stmt->close();
    }
include '../includes/footer.php';
?>