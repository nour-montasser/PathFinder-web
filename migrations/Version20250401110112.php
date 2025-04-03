<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401110112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_user (id_user BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role BIGINT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('CREATE TABLE applicationservice (id_app BIGINT AUTO_INCREMENT NOT NULL, id_service BIGINT DEFAULT NULL, id_user BIGINT DEFAULT NULL, price_offre DOUBLE PRECISION NOT NULL, status VARCHAR(25) NOT NULL, rating TINYINT(1) NOT NULL, INDEX IDX_A89D71943F0033A2 (id_service), INDEX IDX_A89D71946B3CA4B (id_user), PRIMARY KEY(id_app)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE certificates (id_certificate BIGINT AUTO_INCREMENT NOT NULL, id_cv BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(500) NOT NULL, media VARCHAR(255) NOT NULL, issue_date DATE NOT NULL, issued_by VARCHAR(255) NOT NULL, INDEX IDX_8D26FB5F76120795 (id_cv), PRIMARY KEY(id_certificate)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE channel (id_channel BIGINT AUTO_INCREMENT NOT NULL, id_user1 BIGINT DEFAULT NULL, id_user2 BIGINT DEFAULT NULL, rating INT NOT NULL, time_created DATETIME NOT NULL, INDEX IDX_A2F98E4762D4C465 (id_user1), INDEX IDX_A2F98E47FBDD95DF (id_user2), PRIMARY KEY(id_channel)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coverletter (id_cover_letter BIGINT AUTO_INCREMENT NOT NULL, id_app BIGINT DEFAULT NULL, content TEXT NOT NULL, subject VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_DDD8840357CA9895 (id_app), PRIMARY KEY(id_cover_letter)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cv (id_cv BIGINT AUTO_INCREMENT NOT NULL, id_user BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, user_title VARCHAR(255) NOT NULL, introduction VARCHAR(500) NOT NULL, date_creation DATETIME NOT NULL, skills VARCHAR(255) NOT NULL, last_viewed DATETIME NOT NULL, favorite TINYINT(1) NOT NULL, INDEX IDX_B66FFE926B3CA4B (id_user), PRIMARY KEY(id_cv)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experience (id_experience BIGINT AUTO_INCREMENT NOT NULL, id_cv BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, position VARCHAR(255) NOT NULL, location_name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, description VARCHAR(500) NOT NULL, INDEX IDX_590C10376120795 (id_cv), PRIMARY KEY(id_experience)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_offer (id_offer BIGINT AUTO_INCREMENT NOT NULL, id_user BIGINT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, date_posted DATETIME NOT NULL, type VARCHAR(255) NOT NULL, number_of_spots INT NOT NULL, required_education VARCHAR(255) NOT NULL, required_experience VARCHAR(255) NOT NULL, skills VARCHAR(255) NOT NULL, field VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_288A3A4E6B3CA4B (id_user), PRIMARY KEY(id_offer)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE languages (id_language BIGINT AUTO_INCREMENT NOT NULL, id_cv BIGINT DEFAULT NULL, language_name VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_A0D1537976120795 (id_cv), PRIMARY KEY(id_language)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id_message BIGINT AUTO_INCREMENT NOT NULL, id_user_sender BIGINT DEFAULT NULL, id_channel BIGINT DEFAULT NULL, content TEXT NOT NULL, media VARCHAR(255) DEFAULT NULL, time_sent DATETIME NOT NULL, INDEX IDX_B6BD307F22409C05 (id_user_sender), INDEX IDX_B6BD307F7C642737 (id_channel), PRIMARY KEY(id_message)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id_user BIGINT NOT NULL, address VARCHAR(255) NOT NULL, birthday DATE NOT NULL, phone VARCHAR(20) NOT NULL, current_occupation VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, bio TEXT NOT NULL, PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE questions (id_question BIGINT AUTO_INCREMENT NOT NULL, id_test BIGINT DEFAULT NULL, question VARCHAR(1000) NOT NULL, responses VARCHAR(1000) NOT NULL, correct_response VARCHAR(1000) NOT NULL, score INT NOT NULL, INDEX IDX_8ADC54D5535F620E (id_test), PRIMARY KEY(id_question)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report (id_report BIGINT AUTO_INCREMENT NOT NULL, id_user_sender BIGINT DEFAULT NULL, id_user_target BIGINT DEFAULT NULL, description VARCHAR(500) NOT NULL, type VARCHAR(100) NOT NULL, INDEX IDX_C42F778422409C05 (id_user_sender), INDEX IDX_C42F77843B2FF936 (id_user_target), PRIMARY KEY(id_report)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE serviceoffre (id_service BIGINT AUTO_INCREMENT NOT NULL, id_user BIGINT NOT NULL, description LONGTEXT NOT NULL, title VARCHAR(25) NOT NULL, date_posted DATETIME NOT NULL, field VARCHAR(25) NOT NULL, price DOUBLE PRECISION NOT NULL, required_education VARCHAR(100) NOT NULL, skills VARCHAR(100) NOT NULL, experience_level VARCHAR(50) NOT NULL, duration VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id_service)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skilltest (id_test BIGINT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1000) NOT NULL, duration BIGINT NOT NULL, score_required BIGINT NOT NULL, id_JobOffer BIGINT DEFAULT NULL, INDEX IDX_6647AA189B5320A3 (id_JobOffer), PRIMARY KEY(id_test)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE test_result (id_result BIGINT AUTO_INCREMENT NOT NULL, id_user BIGINT NOT NULL, id_test BIGINT NOT NULL, result DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_84B3C63D6B3CA4B (id_user), INDEX IDX_84B3C63D535F620E (id_test), PRIMARY KEY(id_result)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_job (application_id BIGINT NOT NULL, id_user BIGINT DEFAULT NULL, cv_id BIGINT DEFAULT NULL, date_application DATETIME NOT NULL, status VARCHAR(50) NOT NULL, JobOffer_id BIGINT DEFAULT NULL, INDEX IDX_75E7356CC28BCF89 (JobOffer_id), INDEX IDX_75E7356C6B3CA4B (id_user), INDEX IDX_75E7356CCFE419E2 (cv_id), PRIMARY KEY(application_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CC28BCF89 FOREIGN KEY (JobOffer_id) REFERENCES job_offer (idOffer)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv)');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F76120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E4762D4C465 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E47FBDD95DF FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coverletter ADD CONSTRAINT FK_DDD8840357CA9895 FOREIGN KEY (id_app) REFERENCES application_job (application_id)');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C10376120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT FK_A0D1537976120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skilltest ADD CONSTRAINT FK_6647AA189B5320A3 FOREIGN KEY (id_JobOffer) REFERENCES job_offer (idOffer)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CC28BCF89');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356C6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CCFE419E2');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71946B3CA4B');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F76120795');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E4762D4C465');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E47FBDD95DF');
        $this->addSql('ALTER TABLE coverletter DROP FOREIGN KEY FK_DDD8840357CA9895');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE926B3CA4B');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C10376120795');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E6B3CA4B');
        $this->addSql('ALTER TABLE languages DROP FOREIGN KEY FK_A0D1537976120795');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F22409C05');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7C642737');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F6B3CA4B');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5535F620E');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843B2FF936');
        $this->addSql('ALTER TABLE skilltest DROP FOREIGN KEY FK_6647AA189B5320A3');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D535F620E');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE application_job');
        $this->addSql('DROP TABLE applicationservice');
        $this->addSql('DROP TABLE certificates');
        $this->addSql('DROP TABLE channel');
        $this->addSql('DROP TABLE coverletter');
        $this->addSql('DROP TABLE cv');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE languages');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE serviceoffre');
        $this->addSql('DROP TABLE skilltest');
        $this->addSql('DROP TABLE test_result');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
