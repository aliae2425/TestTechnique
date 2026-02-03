<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203192026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_template_question (quiz_template_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_61B574BE2AFC1C18 (quiz_template_id), INDEX IDX_61B574BE1E27F6BF (question_id), PRIMARY KEY (quiz_template_id, question_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz_template_question ADD CONSTRAINT FK_61B574BE2AFC1C18 FOREIGN KEY (quiz_template_id) REFERENCES quiz_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_template_question ADD CONSTRAINT FK_61B574BE1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_template_question DROP FOREIGN KEY FK_61B574BE2AFC1C18');
        $this->addSql('ALTER TABLE quiz_template_question DROP FOREIGN KEY FK_61B574BE1E27F6BF');
        $this->addSql('DROP TABLE quiz_template_question');
    }
}
