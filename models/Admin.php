<?php

namespace Model;

class Admin extends ActiveRecord
{
    public static function findByEmail(string $email): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_admin, rol, fullname, email, password FROM Administrador WHERE email = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        $stmt->close();
        return $admin ?: null;
    }

    public static function updatePasswordByEmail(string $email, string $passwordHash): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Administrador SET password = ? WHERE email = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $passwordHash, $email);
        $ok = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $ok && $affectedRows > 0;
    }
}
