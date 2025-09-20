<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250920072503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__vehicle AS SELECT id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles FROM vehicle');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('CREATE TABLE vehicle (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, merchant_id INTEGER NOT NULL, brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, engine_capacity NUMERIC(5, 2) DEFAULT NULL, colour VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, quantity INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, doors INTEGER DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, beds INTEGER DEFAULT NULL, load_capacity_kg NUMERIC(8, 2) DEFAULT NULL, axles INTEGER DEFAULT NULL, CONSTRAINT FK_1B80E4866796D554 FOREIGN KEY (merchant_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vehicle (id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles) SELECT id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles FROM __temp__vehicle');
        $this->addSql('DROP TABLE __temp__vehicle');
        $this->addSql('CREATE INDEX IDX_1B80E4866796D554 ON vehicle (merchant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__vehicle AS SELECT id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles FROM vehicle');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('CREATE TABLE vehicle (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, merchant_id INTEGER NOT NULL, brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, engine_capacity NUMERIC(5, 2) NOT NULL, colour VARCHAR(50) NOT NULL, price NUMERIC(10, 2) NOT NULL, quantity INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, doors INTEGER DEFAULT NULL, category VARCHAR(50) DEFAULT NULL, beds INTEGER DEFAULT NULL, load_capacity_kg NUMERIC(8, 2) DEFAULT NULL, axles INTEGER DEFAULT NULL, CONSTRAINT FK_1B80E4866796D554 FOREIGN KEY (merchant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO vehicle (id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles) SELECT id, merchant_id, brand, model, engine_capacity, colour, price, quantity, created_at, updated_at, type, doors, category, beds, load_capacity_kg, axles FROM __temp__vehicle');
        $this->addSql('DROP TABLE __temp__vehicle');
        $this->addSql('CREATE INDEX IDX_1B80E4866796D554 ON vehicle (merchant_id)');
    }
}
