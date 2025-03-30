<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330001202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY channel_ibfk_1');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY channel_ibfk_2');
        $this->addSql('CREATE TABLE app_user (id_user BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role BIGINT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_job (application_id BIGINT NOT NULL, id_user BIGINT DEFAULT NULL, cv_id BIGINT DEFAULT NULL, date_application DATETIME NOT NULL, status VARCHAR(50) NOT NULL, JobOffer_id BIGINT DEFAULT NULL, INDEX IDX_75E7356CC28BCF89 (JobOffer_id), INDEX IDX_75E7356C6B3CA4B (id_user), INDEX IDX_75E7356CCFE419E2 (cv_id), PRIMARY KEY(application_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_offer (id_offer BIGINT AUTO_INCREMENT NOT NULL, id_user BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, date_posted DATETIME NOT NULL, type VARCHAR(255) NOT NULL, number_of_spots INT NOT NULL, required_education VARCHAR(255) NOT NULL, required_experience VARCHAR(255) NOT NULL, skills VARCHAR(255) NOT NULL, field VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_288A3A4E6B3CA4B (id_user), PRIMARY KEY(id_offer)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CC28BCF89 FOREIGN KEY (JobOffer_id) REFERENCES job_offer (idOffer)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('DROP TABLE applicationjob');
        $this->addSql('DROP TABLE appuser');
        $this->addSql('DROP TABLE joboffer');
        $this->addSql('ALTER TABLE applicationservice ADD id_user BIGINT DEFAULT NULL, DROP idUser, CHANGE id_app id_app BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_A89D71946B3CA4B ON applicationservice (id_user)');
        $this->addSql('ALTER TABLE certificates CHANGE id_certificate id_certificate BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F76120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8D26FB5F76120795 ON certificates (id_cv)');
        $this->addSql('DROP INDEX IDX_A2F98E4762D4C465 ON channel');
        $this->addSql('DROP INDEX IDX_A2F98E47FBDD95DF ON channel');
        $this->addSql('ALTER TABLE channel ADD id_user1 BIGINT DEFAULT NULL, ADD id_user2 BIGINT DEFAULT NULL, DROP idUser1, DROP idUser2, CHANGE id_channel id_channel BIGINT AUTO_INCREMENT NOT NULL, CHANGE rating rating INT NOT NULL');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E4762D4C465 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E47FBDD95DF FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A2F98E4762D4C465 ON channel (id_user1)');
        $this->addSql('CREATE INDEX IDX_A2F98E47FBDD95DF ON channel (id_user2)');
        $this->addSql('ALTER TABLE coverletter CHANGE id_cover_letter id_cover_letter BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_app id_app BIGINT DEFAULT NULL, CHANGE content content TEXT NOT NULL');
        $this->addSql('ALTER TABLE coverletter ADD CONSTRAINT FK_DDD8840357CA9895 FOREIGN KEY (id_app) REFERENCES application_job (application_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDD8840357CA9895 ON coverletter (id_app)');
        $this->addSql('ALTER TABLE cv ADD id_user BIGINT DEFAULT NULL, DROP idUser, CHANGE id_cv id_cv BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B66FFE926B3CA4B ON cv (id_user)');
        $this->addSql('ALTER TABLE experience CHANGE id_experience id_experience BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C10376120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_590C10376120795 ON experience (id_cv)');
        $this->addSql('ALTER TABLE languages CHANGE id_language id_language BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT FK_A0D1537976120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A0D1537976120795 ON languages (id_cv)');
        $this->addSql('ALTER TABLE message ADD id_user_sender BIGINT DEFAULT NULL, DROP idUser_sender, CHANGE id_message id_message BIGINT AUTO_INCREMENT NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE media media VARCHAR(255) DEFAULT NULL, CHANGE id_channel id_channel BIGINT DEFAULT NULL, CHANGE timesent time_sent DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B6BD307F22409C05 ON message (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_B6BD307F7C642737 ON message (id_channel)');
        $this->addSql('DROP INDEX `primary` ON profile');
        $this->addSql('ALTER TABLE profile CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE bio bio TEXT NOT NULL, CHANGE idUser id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD PRIMARY KEY (id_user)');
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_test id_test BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8ADC54D5535F620E ON questions (id_test)');
        $this->addSql('ALTER TABLE report ADD id_user_sender BIGINT DEFAULT NULL, ADD id_user_target BIGINT DEFAULT NULL, DROP idUser_sender, DROP idUser_target, CHANGE id_report id_report BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C42F778422409C05 ON report (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_C42F77843B2FF936 ON report (id_user_target)');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT AUTO_INCREMENT NOT NULL, CHANGE idUser id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_JobOffer id_JobOffer BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE skilltest ADD CONSTRAINT FK_6647AA189B5320A3 FOREIGN KEY (id_JobOffer) REFERENCES job_offer (idOffer)');
        $this->addSql('CREATE INDEX IDX_6647AA189B5320A3 ON skilltest (id_JobOffer)');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT AUTO_INCREMENT NOT NULL, CHANGE idUser id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('CREATE INDEX IDX_84B3C63D6B3CA4B ON test_result (id_user)');
        $this->addSql('CREATE INDEX IDX_84B3C63D535F620E ON test_result (id_test)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71946B3CA4B');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E4762D4C465');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E47FBDD95DF');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE926B3CA4B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F22409C05');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F6B3CA4B');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843B2FF936');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');
        $this->addSql('ALTER TABLE coverletter DROP FOREIGN KEY FK_DDD8840357CA9895');
        $this->addSql('ALTER TABLE skilltest DROP FOREIGN KEY FK_6647AA189B5320A3');
        $this->addSql('CREATE TABLE applicationjob (application_id BIGINT NOT NULL, JobOffer_id BIGINT NOT NULL, idUser BIGINT NOT NULL, date_application DATETIME NOT NULL, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, cv_id BIGINT NOT NULL, PRIMARY KEY(application_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE appuser (idUser BIGINT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role BIGINT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(idUser)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE joboffer (id_offer BIGINT NOT NULL, idUser BIGINT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(1000) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, number_of_spots INT NOT NULL, required_education VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, required_experience VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, skills VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, field VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_posted DATETIME NOT NULL, PRIMARY KEY(id_offer)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CC28BCF89');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356C6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CCFE419E2');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E6B3CA4B');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE application_job');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP INDEX IDX_A89D71946B3CA4B ON applicationservice');
        $this->addSql('ALTER TABLE applicationservice ADD idUser BIGINT NOT NULL, DROP id_user, CHANGE id_app id_app BIGINT NOT NULL');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F76120795');
        $this->addSql('DROP INDEX IDX_8D26FB5F76120795 ON certificates');
        $this->addSql('ALTER TABLE certificates CHANGE id_certificate id_certificate BIGINT NOT NULL, CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('DROP INDEX IDX_A2F98E4762D4C465 ON channel');
        $this->addSql('DROP INDEX IDX_A2F98E47FBDD95DF ON channel');
        $this->addSql('ALTER TABLE channel ADD idUser1 BIGINT DEFAULT NULL, ADD idUser2 BIGINT DEFAULT NULL, DROP id_user1, DROP id_user2, CHANGE id_channel id_channel BIGINT NOT NULL, CHANGE rating rating BIGINT NOT NULL');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_1 FOREIGN KEY (idUser1) REFERENCES appuser (idUser) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_2 FOREIGN KEY (idUser2) REFERENCES appuser (idUser) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A2F98E4762D4C465 ON channel (idUser1)');
        $this->addSql('CREATE INDEX IDX_A2F98E47FBDD95DF ON channel (idUser2)');
        $this->addSql('DROP INDEX UNIQ_DDD8840357CA9895 ON coverletter');
        $this->addSql('ALTER TABLE coverletter CHANGE id_cover_letter id_cover_letter BIGINT NOT NULL, CHANGE id_app id_app BIGINT NOT NULL, CHANGE content content VARCHAR(5000) NOT NULL');
        $this->addSql('DROP INDEX IDX_B66FFE926B3CA4B ON cv');
        $this->addSql('ALTER TABLE cv ADD idUser BIGINT NOT NULL, DROP id_user, CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C10376120795');
        $this->addSql('DROP INDEX IDX_590C10376120795 ON experience');
        $this->addSql('ALTER TABLE experience CHANGE id_experience id_experience BIGINT NOT NULL, CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE languages DROP FOREIGN KEY FK_A0D1537976120795');
        $this->addSql('DROP INDEX IDX_A0D1537976120795 ON languages');
        $this->addSql('ALTER TABLE languages CHANGE id_language id_language BIGINT NOT NULL, CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7C642737');
        $this->addSql('DROP INDEX IDX_B6BD307F22409C05 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F7C642737 ON message');
        $this->addSql('ALTER TABLE message ADD idUser_sender BIGINT NOT NULL, DROP id_user_sender, CHANGE id_message id_message BIGINT NOT NULL, CHANGE id_channel id_channel BIGINT NOT NULL, CHANGE content content VARCHAR(500) NOT NULL, CHANGE media media VARCHAR(255) NOT NULL, CHANGE time_sent timesent DATETIME NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON profile');
        $this->addSql('ALTER TABLE profile CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE bio bio VARCHAR(500) NOT NULL, CHANGE id_user idUser BIGINT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD PRIMARY KEY (idUser)');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5535F620E');
        $this->addSql('DROP INDEX IDX_8ADC54D5535F620E ON questions');
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question BIGINT NOT NULL, CHANGE id_test id_test BIGINT NOT NULL');
        $this->addSql('DROP INDEX IDX_C42F778422409C05 ON report');
        $this->addSql('DROP INDEX IDX_C42F77843B2FF936 ON report');
        $this->addSql('ALTER TABLE report ADD idUser_sender BIGINT NOT NULL, ADD idUser_target BIGINT NOT NULL, DROP id_user_sender, DROP id_user_target, CHANGE id_report id_report BIGINT NOT NULL');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT NOT NULL, CHANGE id_user idUser BIGINT NOT NULL');
        $this->addSql('DROP INDEX IDX_6647AA189B5320A3 ON skilltest');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT NOT NULL, CHANGE id_JobOffer id_JobOffer BIGINT NOT NULL');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D535F620E');
        $this->addSql('DROP INDEX IDX_84B3C63D6B3CA4B ON test_result');
        $this->addSql('DROP INDEX IDX_84B3C63D535F620E ON test_result');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT NOT NULL, CHANGE id_user idUser BIGINT NOT NULL');
    }
}
