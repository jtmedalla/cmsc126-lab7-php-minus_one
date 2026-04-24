<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/DBConnector.php";

const MAX_IMAGE_SIZE = 2097152;

function jsonResponse(int $statusCode, bool $success, string $message, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data,
    ]);
    exit;
}

function parseBool(string $value): int
{
    return $value === "1" || strtolower($value) === "true" || strtolower($value) === "on" ? 1 : 0;
}

function validateStudentPayload(array $payload): array
{
    $name = trim((string) ($payload["name"] ?? ""));
    $age = (int) ($payload["age"] ?? -1);
    $email = trim((string) ($payload["email"] ?? ""));
    $course = trim((string) ($payload["course"] ?? ""));
    $yearLevel = (int) ($payload["year_level"] ?? 0);
    $graduating = parseBool((string) ($payload["graduating"] ?? "0"));

    if ($name === "" || strlen($name) > 40) {
        throw new InvalidArgumentException("Name is required and must not exceed 40 characters.");
    }

    if ($age < 0 || $age > 99) {
        throw new InvalidArgumentException("Age must be between 0 and 99.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 40) {
        throw new InvalidArgumentException("Email must be valid and must not exceed 40 characters.");
    }

    if ($course === "" || strlen($course) > 40) {
        throw new InvalidArgumentException("Course is required and must not exceed 40 characters.");
    }

    if (!in_array($yearLevel, [1, 2, 3, 4], true)) {
        throw new InvalidArgumentException("Year level must be 1, 2, 3, or 4.");
    }

    return [
        "name" => $name,
        "age" => $age,
        "email" => $email,
        "course" => $course,
        "year_level" => $yearLevel,
        "graduating" => $graduating,
    ];
}

function ensureUploadDirectory(): string
{
    $uploadDir = __DIR__ . "/uploads";

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException("Unable to create upload directory.");
    }

    return $uploadDir;
}

function storeImage(array $file): string
{
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException("Image upload failed.");
    }

    if (($file["size"] ?? 0) <= 0 || ($file["size"] ?? 0) > MAX_IMAGE_SIZE) {
        throw new InvalidArgumentException("Image must be between 1 byte and 2MB.");
    }

    $tmpName = (string) ($file["tmp_name"] ?? "");
    $mime = mime_content_type($tmpName);
    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
        "image/gif" => "gif",
    ];

    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException("Only JPG, PNG, WEBP, and GIF files are allowed.");
    }

    $uploadDir = ensureUploadDirectory();
    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
    $targetPath = $uploadDir . "/" . $filename;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException("Failed to save uploaded image.");
    }

    return "uploads/" . $filename;
}

function deleteImageIfExists(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }

    $relativePath = str_replace("\\", "/", $relativePath);
    if (str_starts_with($relativePath, "uploads/")) {
        $fullPath = __DIR__ . "/" . $relativePath;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

try {
    $action = $_REQUEST["action"] ?? "";

    if ($action === "") {
        jsonResponse(400, false, "Missing action.");
    }

    if ($action === "create") {
        $student = validateStudentPayload($_POST);
        if (!isset($_FILES["image"])) {
            throw new InvalidArgumentException("Profile image is required.");
        }

        $imagePath = storeImage($_FILES["image"]);
        $student["image_path"] = $imagePath;

        try {
            $id = createStudent($student);
            $record = getStudentById($id);
            jsonResponse(201, true, "Student created successfully.", ["student" => $record]);
        } catch (Throwable $e) {
            deleteImageIfExists($imagePath);
            throw $e;
        }
    }

    if ($action === "search") {
        $id = (int) ($_GET["id"] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException("A valid student ID is required.");
        }

        $student = getStudentById($id);
        if (!$student) {
            jsonResponse(404, false, "Student not found.");
        }

        jsonResponse(200, true, "Student found.", ["student" => $student]);
    }

    if ($action === "update") {
        $id = (int) ($_POST["id"] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException("A valid student ID is required.");
        }

        $existing = getStudentById($id);
        if (!$existing) {
            jsonResponse(404, false, "Student not found.");
        }

        $student = validateStudentPayload($_POST);
        $newImagePath = null;
        $student["image_path"] = (string) $existing["image_path"];

        if (isset($_FILES["image"]) && ($_FILES["image"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $newImagePath = storeImage($_FILES["image"]);
            $student["image_path"] = $newImagePath;
        }

        try {
            updateStudentById($id, $student);
            if ($newImagePath !== null) {
                deleteImageIfExists((string) $existing["image_path"]);
            }
            $record = getStudentById($id);
            jsonResponse(200, true, "Student updated successfully.", ["student" => $record]);
        } catch (Throwable $e) {
            if ($newImagePath !== null) {
                deleteImageIfExists($newImagePath);
            }
            throw $e;
        }
    }

    if ($action === "delete") {
        $id = (int) ($_POST["id"] ?? 0);
        if ($id <= 0) {
            throw new InvalidArgumentException("A valid student ID is required.");
        }

        $existing = getStudentById($id);
        if (!$existing) {
            jsonResponse(404, false, "Student not found.");
        }

        $deleted = deleteStudentById($id);
        if (!$deleted) {
            throw new RuntimeException("Delete operation did not remove any record.");
        }

        deleteImageIfExists((string) $existing["image_path"]);
        jsonResponse(200, true, "Student deleted successfully.");
    }

    jsonResponse(400, false, "Invalid action.");
} catch (InvalidArgumentException $e) {
    jsonResponse(422, false, $e->getMessage());
} catch (Throwable $e) {
    jsonResponse(500, false, $e->getMessage());
}

?>