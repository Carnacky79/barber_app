<?php
// includes/footer.php - Footer per le pagine utente
$current_year = date('Y');
?>
<footer>
    <div class="footer-content">
        <div class="footer-links">
            <a href="chi_siamo.php">Chi Siamo</a>
            <a href="termini.php">Termini e Condizioni</a>
            <a href="privacy.php">Privacy Policy</a>
            <a href="contatti.php">Contatti</a>
        </div>
        <div class="copyright">
            &copy; <?php echo $current_year; ?> BarberBook - Tutti i diritti riservati
        </div>
    </div>
</footer>
