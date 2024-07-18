<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240718093021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user, customer, and product tables with the correct schema';
    }

    public function up(Schema $schema): void
    {
        // Create the user table
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create the customer table
        $this->addSql('CREATE TABLE customer (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            address VARCHAR(255) NOT NULL,
            INDEX IDX_81398E09A76ED395 (user_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_81398E09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create the product table
        $this->addSql('CREATE TABLE product (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description VARCHAR(255) NOT NULL,
            brand VARCHAR(255) NOT NULL,
            model DECIMAL(2, 2) NOT NULL,
            price INT NOT NULL,
            stock INT NOT NULL,
            date_added DATETIME NOT NULL,
            technical_specs JSON DEFAULT NULL,
            images JSON DEFAULT NULL,
            category VARCHAR(255) DEFAULT NULL,
            available_colors JSON DEFAULT NULL,
            state VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS customer');
        $this->addSql('DROP TABLE IF EXISTS product');
        $this->addSql('DROP TABLE IF EXISTS user');
    }
}
