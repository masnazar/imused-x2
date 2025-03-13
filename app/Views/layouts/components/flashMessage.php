<?php
// Ambil nilai useSweetAlert dari setData atau gunakan default true
$useSweetAlert = $useSweetAlert ?? true;

// Ambil pesan flash jika ada
$flashMessage = session('success') ?? session('error') ?? session('warning') ?? null;

// Jika tidak ada pesan flash, keluar dari script
if (!$flashMessage) {
    return;
}
?>

<?php if ($useSweetAlert) : ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: "<?= session()->has('success') ? 'success' : (session()->has('error') ? 'error' : 'warning') ?>",
                title: "<?= session()->has('success') ? 'Berhasil!' : (session()->has('error') ? 'Gagal!' : 'Peringatan!') ?>",
                text: "<?= esc($flashMessage) ?>",
                showConfirmButton: false,
                timer: 3000
            });
        });
    </script>
<?php else : ?>
    <div class="alert alert-<?= session()->has('success') ? 'success' : (session()->has('error') ? 'danger' : 'warning') ?> alert-dismissible fade show" role="alert">
        <?= esc($flashMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
