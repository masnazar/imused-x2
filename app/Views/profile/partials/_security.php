<div class="tab-pane p-0 border-0" id="security" role="tabpanel">
    <?= form_open('profile/update-password', ['class' => 'needs-validation']) ?>
    <?= csrf_field() ?>
    
    <ul class="list-group list-group-flush border rounded-3">
        <li class="list-group-item p-3">
            <div class="mb-3">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </li>
    </ul>
    
    <div class="p-3">
        <button type="submit" class="btn btn-danger">Ubah Password</button>
    </div>
    <?= form_close() ?>
</div>