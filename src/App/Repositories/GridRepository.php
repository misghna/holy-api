<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

class GridRepository
{
    public function __construct(private Database $database)
    {
    }

    public function getAll(): array
    {
        $pdo = $this->database->getConnection();

        $stmt = $pdo->query('SELECT * FROM grid_details');
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|bool
    {
        $sql = 'SELECT *
                FROM grid_details
                WHERE id = :id';

        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO grid_details (type, background_image, content, media_link, content_category)
                VALUES (:type, :background_image, :content, :media_link, :content_category)';

        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare($sql);
        if (empty($data['type'])) {
            $stmt->bindValue(':type', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':type', $data['type'], PDO::PARAM_STR);
        }
        if (empty($data['background_image'])) {
            $stmt->bindValue(':background_image', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':background_image', $data['background_image'], PDO::PARAM_STR);
        }
        if (empty($data['content'])) {
            $stmt->bindValue(':content', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        }
        if (empty($data['media_link'])) {
            $stmt->bindValue(':media_link', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':media_link', $data['media_link'], PDO::PARAM_STR);
        }
        if (empty($data['content_category'])) {
            $stmt->bindValue(':content_category', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':content_category', $data['content_category'], PDO::PARAM_STR);
        }

        $stmt->execute();

        return $pdo->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        $sql = 'UPDATE grid_details
                SET type = :type,
                background_image = :background_image,
                content = :content,
                media_link = :media_link,
                content_category = :content_category
                WHERE id = :id';

        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);

        if (empty($data['type'])) {
            $stmt->bindValue(':type', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':type', $data['type'], PDO::PARAM_STR);
        }
        if (empty($data['background_image'])) {
            $stmt->bindValue(':background_image', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':background_image', $data['background_image'], PDO::PARAM_STR);
        }
        if (empty($data['content'])) {
            $stmt->bindValue(':content', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':content', $data['content'], PDO::PARAM_STR);
        }
        if (empty($data['media_link'])) {
            $stmt->bindValue(':media_link', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':media_link', $data['media_link'], PDO::PARAM_STR);
        }
        if (empty($data['content_category'])) {
            $stmt->bindValue(':content_category', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':content_category', $data['content_category'], PDO::PARAM_STR);
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $sql = 'DELETE FROM grid_details
                WHERE id = :id';

        $pdo = $this->database->getConnection();

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }
}