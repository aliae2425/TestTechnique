<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223200845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E979B1AD6 ON question (company_id)');
        $this->addSql('ALTER TABLE quiz_template ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_template ADD CONSTRAINT FK_41A4E6C6979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_41A4E6C6979B1AD6 ON quiz_template (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E979B1AD6');
        $this->addSql('DROP INDEX IDX_B6F7494E979B1AD6 ON question');
        $this->addSql('ALTER TABLE question DROP company_id');
        $this->addSql('ALTER TABLE quiz_template DROP FOREIGN KEY FK_41A4E6C6979B1AD6');
        $this->addSql('DROP INDEX IDX_41A4E6C6979B1AD6 ON quiz_template');
        $this->addSql('ALTER TABLE quiz_template DROP company_id');
    }
}
