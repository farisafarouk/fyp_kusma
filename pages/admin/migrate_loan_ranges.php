<?php
require_once '../../config/database.php'; // adjust path if needed

function parseLoanRange($setString) {
    $ranges = explode(',', $setString);
    $mins = [];
    $maxs = [];

    foreach ($ranges as $range) {
        $range = trim($range);
        if (strpos($range, '+') !== false) {
            $mins[] = (int)filter_var($range, FILTER_SANITIZE_NUMBER_INT);
            $maxs[] = 10000000; // Arbitrary high value for +
        } else {
            [$min, $max] = explode(' - ', $range);
            $mins[] = (int)trim($min);
            $maxs[] = (int)trim($max);
        }
    }

    return [min($mins), max($maxs)];
}

$result = $conn->query("SELECT id, loan_amount_range FROM programs");

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $rangeSet = $row['loan_amount_range'];

    if (!empty($rangeSet)) {
        [$min, $max] = parseLoanRange($rangeSet);
        $stmt = $conn->prepare("UPDATE programs SET min_loan_amount = ?, max_loan_amount = ? WHERE id = ?");
        $stmt->bind_param("iii", $min, $max, $id);
        $stmt->execute();
        $stmt->close();
    }
}

echo "<h3>✅ Migration complete. All loan ranges updated successfully.</h3>";
?>
