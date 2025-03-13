<div class="tab-pane show active p-0 border-0" id="about" role="tabpanel">
    <ul class="list-group list-group-flush border rounded-3">
        <li class="list-group-item p-3">
            <span class="fw-medium fs-15 d-block mb-3"><span class="me-1">📖</span>Tentang Saya</span>
            <p class="text-muted mb-0">
                <?= $user->bio ? nl2br(esc($user->bio)) : 'Belum ada deskripsi profil.' ?>
            </p>
        </li>
    </ul>
</div>