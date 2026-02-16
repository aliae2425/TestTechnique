<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216195200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adress (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(255) NOT NULL, number INT NOT NULL, zip_code VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, company_id INT NOT NULL, INDEX IDX_5CECC7BE979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE adress ADD CONSTRAINT FK_5CECC7BE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE company ADD contry VARCHAR(255) NOT NULL, ADD activity_sector VARCHAR(255) NOT NULL, ADD size VARCHAR(255) NOT NULL, ADD logo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adress DROP FOREIGN KEY FK_5CECC7BE979B1AD6');
        $this->addSql('DROP TABLE adress');
        $this->addSql('ALTER TABLE company DROP contry, DROP activity_sector, DROP size, DROP logo');
    }
}
