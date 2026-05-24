CREATE TABLE `service_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_name` VARCHAR(255) NOT NULL,
    `total_price` DECIMAL(10, 2) NOT NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_by` INT,
    `updated_at` DATETIME
);


CREATE TABLE `service_package_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    FOREIGN KEY (`package_id`) REFERENCES `service_packages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `service_items`(`id`) ON DELETE CASCADE
);
