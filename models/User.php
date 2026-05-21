<?php
require_once __DIR__ . '/Model.php';

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT id, name, email, password, phone, role, status FROM users WHERE email = ?', 's', [$email]);
    }

    public function create(string $name, string $email, string $password, string $role = 'customer', string $phone = null, string $status = null): int
    {
        if ($status === null) {
            $status = ($role === 'customer') ? 'active' : 'inactive';
        }
        $this->execute(
            'INSERT INTO users (name, email, password, password_hash, phone, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'ssssssss',
            [$name, $email, $password, $password, $phone, $role, $status, date('Y-m-d H:i:s')]
        );
        return $this->insertId();
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, name, email, phone, role, status, vehicle_type, profile_picture, is_available FROM users WHERE id = ?',
            'i',
            [$id]
        );
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT id, name, email, phone, role, status, created_at FROM users ORDER BY created_at DESC');
    }

    public function updateStatus(int $id, string $status): int
    {
        return $this->execute('UPDATE users SET status = ?, is_active = ? WHERE id = ?', 'sii', [$status, $status === 'active' ? 1 : 0, $id]);
    }

    public function updateAvailability(int $id, int $isAvailable): int
    {
        return $this->execute('UPDATE users SET is_available = ? WHERE id = ?', 'ii', [$isAvailable, $id]);
    }

    public function updateDeliveryProfile(int $id, string $name, string $phone, string $vehicleType, ?string $profilePicture): int
    {
        if ($profilePicture !== null) {
            return $this->execute(
                'UPDATE users SET name = ?, phone = ?, vehicle_type = ?, profile_picture = ? WHERE id = ?',
                'ssssi',
                [$name, $phone, $vehicleType, $profilePicture, $id]
            );
        }
        return $this->execute(
            'UPDATE users SET name = ?, phone = ?, vehicle_type = ? WHERE id = ?',
            'sssi',
            [$name, $phone, $vehicleType, $id]
        );
    }
}
