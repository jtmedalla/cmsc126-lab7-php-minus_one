<?php

declare(strict_types=1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab7";

function getDBConnection(): mysqli
{
    global $servername, $username, $password, $dbname;

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new RuntimeException("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function createStudent(array $student): int
{
    $conn = getDBConnection();
    $sql = "INSERT INTO students (name, age, email, course, year_level, graduating, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        throw new RuntimeException("Failed to prepare create statement.");
    }

    $stmt->bind_param(
        "sissiis",
        $student["name"],
        $student["age"],
        $student["email"],
        $student["course"],
        $student["year_level"],
        $student["graduating"],
        $student["image_path"]
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        throw new RuntimeException("Failed to create student: " . $error);
    }

    $newId = (int) $stmt->insert_id;
    $stmt->close();
    $conn->close();

    return $newId;
}

function getStudentById(int $id): ?array
{
    $conn = getDBConnection();
    $sql = "SELECT id, name, age, email, course, year_level, graduating, image_path, created_at, updated_at FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        throw new RuntimeException("Failed to prepare search statement.");
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        throw new RuntimeException("Failed to search student: " . $error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $row ?: null;
}

function updateStudentById(int $id, array $student): bool
{
    $conn = getDBConnection();
    $sql = "UPDATE students SET name = ?, age = ?, email = ?, course = ?, year_level = ?, graduating = ?, image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        throw new RuntimeException("Failed to prepare update statement.");
    }

    $stmt->bind_param(
        "sissiisi",
        $student["name"],
        $student["age"],
        $student["email"],
        $student["course"],
        $student["year_level"],
        $student["graduating"],
        $student["image_path"],
        $id
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        throw new RuntimeException("Failed to update student: " . $error);
    }

    $updated = $stmt->affected_rows >= 0;
    $stmt->close();
    $conn->close();

    return $updated;
}

function deleteStudentById(int $id): bool
{
    $conn = getDBConnection();
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        throw new RuntimeException("Failed to prepare delete statement.");
    }

    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        throw new RuntimeException("Failed to delete student: " . $error);
    }

    $deleted = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();

    return $deleted;
}

?>