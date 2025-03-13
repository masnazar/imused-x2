<div class="card custom-card overflow-hidden border">
    <div class="card-body border-bottom border-block-end-dashed">
        <div class="text-center">
            <span class="avatar avatar-xxl avatar-rounded online mb-3">
                <img src="<?= base_url('uploads/' . ($user->profile_image ?? 'default.jpg')); ?>" alt="Foto Profil">
            </span>
            <h5 class="fw-semibold mb-1"><?= esc($user->name); ?></h5>
            <span class="d-block fw-medium text-muted mb-2"><?= esc($userRole); ?></span>
        </div>
    </div>
    
    <div class="card-body border-bottom border-block-end-dashed p-0">
        <ul class="list-group list-group-flush">
            <li class="list-group-item pt-2 border-0">
                <div><span class="fw-medium me-2">Email:</span><?= esc($user->email); ?></div>
            </li>
            <li class="list-group-item pt-2 border-0">
                <div><span class="fw-medium me-2">WhatsApp:</span>
                    <?= $user->whatsapp_number ? '<a href="https://wa.me/'.esc($user->whatsapp_number).'" class="text-primary">'.esc($user->whatsapp_number).'</a>' : '-' ?>
                </div>
            </li>
            <li class="list-group-item pt-2 border-0">
                <div><span class="fw-medium me-2">Usia:</span><?= $age ?? '-'; ?> Tahun</div>
            </li>
        </ul>
    </div>
</div>