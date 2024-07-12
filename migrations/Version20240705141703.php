<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240705141703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create product table with necessary columns';
    }

    public function up(Schema $schema): void
    {
        // Créer la table product avec toutes les colonnes nécessaires
        $this->addSql('
            CREATE TABLE product (
                id INT AUTO_INCREMENT NOT NULL,
                product_list VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                description VARCHAR(255) NOT NULL,
                brand VARCHAR(255) NOT NULL,
                model NUMERIC(2, 2) NOT NULL,
                price INT NOT NULL,
                stock INT NOT NULL,
                date_added DATETIME NOT NULL,
                technical_specs JSON DEFAULT NULL,
                images JSON DEFAULT NULL,
                category VARCHAR(255) DEFAULT NULL,
                available_colors JSON DEFAULT NULL,
                state VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
        ');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table product
        $this->addSql('DROP TABLE product');
    }
}
