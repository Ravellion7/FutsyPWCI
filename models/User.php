<?php

namespace Model;

class User extends ActiveRecord
{
    public static function findByEmail(string $email): ?array
    {
        $db = static::getDB();
        $sql = 'SELECT id_collector, rol, fullname, email, password, avatar FROM Coleccionista WHERE email = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();
        return $user ?: null;
    }

    public static function create(string $name, string $email, string $passwordHash): bool
    {
        $db = static::getDB();
        $sql = 'INSERT INTO Coleccionista (fullname, email, password) VALUES (?, ?, ?)';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sss', $name, $email, $passwordHash);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function updatePasswordByEmail(string $email, string $passwordHash): bool
    {
        $db = static::getDB();
        $sql = 'UPDATE Coleccionista SET password = ? WHERE email = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $passwordHash, $email);
        $ok = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        return $ok && $affectedRows > 0;
    }
}
