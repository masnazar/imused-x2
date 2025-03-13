<div class="tab-pane p-0 border-0" id="edit" role="tabpanel">
    <?= form_open_multipart('profile/update', ['class' => 'needs-validation']) ?>
    <?= csrf_field() ?>
    
    <ul class="list-group list-group-flush border rounded-3">
        <li class="list-group-item p-3">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="<?= old('name', esc($user->name)); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" class="form-control" value="<?= old('whatsapp_number', esc($user->whatsapp_number)); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
            <input type="email" 
                name="email" 
                class="form-control <?= (validation_show_error('email')) ? 'is-invalid' : '' ?>" 
                value="<?= old('email', $user->email) ?>">
            </div>

            
            <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= old('bio', esc($user->bio)); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Foto Profil</label>
                <input type="file" name="profile_picture" class="form-control">
            </div>
        </li>
    </ul>
    
    <div class="p-3">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
    <?= form_close() ?>
</div>