<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203214647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_session ADD quiz_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_session ADD CONSTRAINT FK_C21E78742AFC1C18 FOREIGN KEY (quiz_template_id) REFERENCES quiz_template (id)');
        $this->addSql('CREATE INDEX IDX_C21E78742AFC1C18 ON quiz_session (quiz_template_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_session DROP FOREIGN KEY FK_C21E78742AFC1C18');
        $this->addSql('DROP INDEX IDX_C21E78742AFC1C18 ON quiz_session');
        $this->addSql('ALTER TABLE quiz_session DROP quiz_template_id');
    }
}
