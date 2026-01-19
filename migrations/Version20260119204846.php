<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119204846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, is_correct TINYINT NOT NULL, feedback LONGTEXT NOT NULL, question_id INT NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fixed_quiz (id INT AUTO_INCREMENT NOT NULL, order_index LONGTEXT NOT NULL, template_id INT NOT NULL, INDEX IDX_C5F8F2015DA0FB8 (template_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fixed_quiz_question (fixed_quiz_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_24B6F0E3F26F56D3 (fixed_quiz_id), INDEX IDX_24B6F0E31E27F6BF (question_id), PRIMARY KEY (fixed_quiz_id, question_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE invitation (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, quiz_template_id INT DEFAULT NULL, INDEX IDX_F11D61A22AFC1C18 (quiz_template_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, titled VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_rule (id INT AUTO_INCREMENT NOT NULL, theme VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, quantity INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_session (id INT AUTO_INCREMENT NOT NULL, start_at DATETIME NOT NULL, final_score DOUBLE PRECISION NOT NULL, user_id INT DEFAULT NULL, invitation_id INT DEFAULT NULL, INDEX IDX_C21E7874A76ED395 (user_id), UNIQUE INDEX UNIQ_C21E7874A35D7AF0 (invitation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_template (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, mode VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, time_limit INT DEFAULT NULL, rules_id INT DEFAULT NULL, INDEX IDX_41A4E6C6FB699244 (rules_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_reponses (id INT AUTO_INCREMENT NOT NULL, time_spent DOUBLE PRECISION NOT NULL, session_id INT NOT NULL, question_id INT NOT NULL, reponse_id INT DEFAULT NULL, INDEX IDX_FEDCCAC6613FECDF (session_id), INDEX IDX_FEDCCAC61E27F6BF (question_id), INDEX IDX_FEDCCAC6CF18BB82 (reponse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE fixed_quiz ADD CONSTRAINT FK_C5F8F2015DA0FB8 FOREIGN KEY (template_id) REFERENCES quiz_template (id)');
        $this->addSql('ALTER TABLE fixed_quiz_question ADD CONSTRAINT FK_24B6F0E3F26F56D3 FOREIGN KEY (fixed_quiz_id) REFERENCES fixed_quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fixed_quiz_question ADD CONSTRAINT FK_24B6F0E31E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A22AFC1C18 FOREIGN KEY (quiz_template_id) REFERENCES quiz_template (id)');
        $this->addSql('ALTER TABLE quiz_session ADD CONSTRAINT FK_C21E7874A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_session ADD CONSTRAINT FK_C21E7874A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES invitation (id)');
        $this->addSql('ALTER TABLE quiz_template ADD CONSTRAINT FK_41A4E6C6FB699244 FOREIGN KEY (rules_id) REFERENCES quiz_rule (id)');
        $this->addSql('ALTER TABLE user_reponses ADD CONSTRAINT FK_FEDCCAC6613FECDF FOREIGN KEY (session_id) REFERENCES quiz_session (id)');
        $this->addSql('ALTER TABLE user_reponses ADD CONSTRAINT FK_FEDCCAC61E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE user_reponses ADD CONSTRAINT FK_FEDCCAC6CF18BB82 FOREIGN KEY (reponse_id) REFERENCES answer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE fixed_quiz DROP FOREIGN KEY FK_C5F8F2015DA0FB8');
        $this->addSql('ALTER TABLE fixed_quiz_question DROP FOREIGN KEY FK_24B6F0E3F26F56D3');
        $this->addSql('ALTER TABLE fixed_quiz_question DROP FOREIGN KEY FK_24B6F0E31E27F6BF');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A22AFC1C18');
        $this->addSql('ALTER TABLE quiz_session DROP FOREIGN KEY FK_C21E7874A76ED395');
        $this->addSql('ALTER TABLE quiz_session DROP FOREIGN KEY FK_C21E7874A35D7AF0');
        $this->addSql('ALTER TABLE quiz_template DROP FOREIGN KEY FK_41A4E6C6FB699244');
        $this->addSql('ALTER TABLE user_reponses DROP FOREIGN KEY FK_FEDCCAC6613FECDF');
        $this->addSql('ALTER TABLE user_reponses DROP FOREIGN KEY FK_FEDCCAC61E27F6BF');
        $this->addSql('ALTER TABLE user_reponses DROP FOREIGN KEY FK_FEDCCAC6CF18BB82');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE fixed_quiz');
        $this->addSql('DROP TABLE fixed_quiz_question');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz_rule');
        $this->addSql('DROP TABLE quiz_session');
        $this->addSql('DROP TABLE quiz_template');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_reponses');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
