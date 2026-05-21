<?php
$isAvailable = (int)($agent['is_available'] ?? 1);
$picturePath = $agent['profile_picture'] ?? null;
$pictureSrc  = $picturePath ? '../' . htmlspecialchars($picturePath, ENT_QUOTES) : null;
?>

<section class="section-title">
    <h2>👤 My Profile</h2>
    <a href="index.php?route=delivery" class="btn-secondary">← Back</a>
</section>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?php echo sanitize($message); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo sanitize($error); ?></div>
<?php endif; ?>

<div class="grid" style="grid-template-columns:1fr 2fr;gap:24px;">

    <div class="card">
        <div class="card-body" style="text-align:center;">
            <?php if ($pictureSrc): ?>
                <img src="<?php echo $pictureSrc; ?>" alt="Profile"
                     style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #e67e22;margin-bottom:16px;">
            <?php else: ?>
                <div style="width:120px;height:120px;border-radius:50%;background:#e67e22;color:#fff;font-size:3rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <?php echo strtoupper(substr($agent['name'] ?? 'A', 0, 1)); ?>
                </div>
            <?php endif; ?>

            <h3 style="margin:0 0 4px;"><?php echo sanitize($agent['name'] ?? ''); ?></h3>
            <p style="margin:0 0 8px;color:#888;"><?php echo sanitize($agent['email'] ?? ''); ?></p>
            <span class="badge-status <?php echo $isAvailable ? 'badge-online' : 'badge-offline'; ?>">
                <?php echo $isAvailable ? '🟢 Online' : '🔴 Offline'; ?>
            </span>
            <p style="color:#888;font-size:.85rem;margin-top:12px;">🚗 <?php echo sanitize($agent['vehicle_type'] ?? 'Not set'); ?></p>
            <p style="color:#888;font-size:.85rem;">📞 <?php echo sanitize($agent['phone'] ?? 'Not set'); ?></p>

            <form method="post" action="index.php?route=delivery&action=profile" style="margin-top:16px;">
                <input type="hidden" name="action" value="toggle_availability">
                <button type="submit" class="<?php echo $isAvailable ? 'btn-danger' : 'btn-accept'; ?>" style="width:100%;">
                    <?php echo $isAvailable ? '🔴 Go Offline' : '🟢 Go Online'; ?>
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="margin-top:0;">Edit Profile</h3>
            <form method="post" action="index.php?route=delivery&action=profile" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" class="form-control"
                           value="<?php echo sanitize($agent['name'] ?? ''); ?>" >
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?php echo sanitize($agent['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="vehicle_type" class="form-control">
                        <?php foreach (['Motorcycle','Bicycle','Car','Scooter','On Foot'] as $v): ?>
                            <option value="<?php echo $v; ?>" <?php echo ($agent['vehicle_type'] ?? '') === $v ? 'selected' : ''; ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <?php if ($pictureSrc): ?>
                        <div style="margin-bottom:8px;">
                            <img src="<?php echo $pictureSrc; ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #e67e22;">
                            <small style="color:#888;margin-left:8px;">Current photo</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                    <small style="color:#888;">JPG, PNG, or WEBP. Max 2MB.</small>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;margin-top:8px;">💾 Save Changes</button>
            </form>
        </div>
    </div>
</div>