<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203193646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_template_quiz_rule (quiz_template_id INT NOT NULL, quiz_rule_id INT NOT NULL, INDEX IDX_3143E0B62AFC1C18 (quiz_template_id), INDEX IDX_3143E0B66614AEF1 (quiz_rule_id), PRIMARY KEY (quiz_template_id, quiz_rule_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz_template_quiz_rule ADD CONSTRAINT FK_3143E0B62AFC1C18 FOREIGN KEY (quiz_template_id) REFERENCES quiz_template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_template_quiz_rule ADD CONSTRAINT FK_3143E0B66614AEF1 FOREIGN KEY (quiz_rule_id) REFERENCES quiz_rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_template DROP FOREIGN KEY `FK_41A4E6C6FB699244`');
        $this->addSql('DROP INDEX IDX_41A4E6C6FB699244 ON quiz_template');
        $this->addSql('ALTER TABLE quiz_template DROP rules_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_template_quiz_rule DROP FOREIGN KEY FK_3143E0B62AFC1C18');
        $this->addSql('ALTER TABLE quiz_template_quiz_rule DROP FOREIGN KEY FK_3143E0B66614AEF1');
        $this->addSql('DROP TABLE quiz_template_quiz_rule');
        $this->addSql('ALTER TABLE quiz_template ADD rules_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_template ADD CONSTRAINT `FK_41A4E6C6FB699244` FOREIGN KEY (rules_id) REFERENCES quiz_rule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_41A4E6C6FB699244 ON quiz_template (rules_id)');
    }
}
