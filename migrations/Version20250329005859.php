<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329005859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'auto increment id_user and other IDs in various tables';
    }


    public function up(Schema $schema): void
    {
        // Temporarily drop foreign keys before altering ID fields
        // We'll use conditional drops to handle cases where FKs might already be dropped
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        
        // For each foreign key, we'll first check if it exists before trying to drop it
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY IF EXISTS FK_8157AA0F6B3CA4B');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY IF EXISTS FK_B66FFE926B3CA4B');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY IF EXISTS FK_590C10376120795');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY IF EXISTS FK_288A3A4E6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356CC28BCF89');
        $this->addSql('ALTER TABLE coverletter DROP FOREIGN KEY IF EXISTS FK_DDD8840357CA9895');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY IF EXISTS FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY IF EXISTS FK_A89D71946B3CA4B');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY IF EXISTS FK_8ADC54D5535F620E');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY IF EXISTS FK_84B3C63D6B3CA4B');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY IF EXISTS FK_84B3C63D535F620E');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY IF EXISTS channel_ibfk_1');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY IF EXISTS channel_ibfk_2');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY IF EXISTS FK_B6BD307F22409C05');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY IF EXISTS FK_B68D307F7C642737');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY IF EXISTS FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY IF EXISTS FK_C42F77843B2FF936');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY IF EXISTS FK_8D26FB5F76120795');
        $this->addSql('ALTER TABLE languages DROP FOREIGN KEY IF EXISTS FK_A001537976120795');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356C6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356CCFE419E2');

        // Alter the app_user table to update id_user
        $this->addSql('ALTER TABLE app_user CHANGE id_user id_user BIGINT AUTO_INCREMENT NOT NULL');

        // Alter the profile table to update id_user
        $this->addSql('ALTER TABLE profile CHANGE id_user id_user BIGINT NOT NULL');

        // Alter the cv table to update id_user and id_cv
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user BIGINT NOT NULL, CHANGE id_cv id_cv BIGINT AUTO_INCREMENT NOT NULL');

        // Alter the experience table to update id_cv and id_experience
        $this->addSql('ALTER TABLE experience CHANGE id_cv id_cv BIGINT NOT NULL, CHANGE id_experience id_experience BIGINT AUTO_INCREMENT NOT NULL');

        // Alter the job_offer table to update id_offer and id_user
        $this->addSql('ALTER TABLE job_offer CHANGE id_offer id_offer BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL');

        // Alter the application_job table to update application_id and job_offer_id
        $this->addSql('ALTER TABLE application_job CHANGE application_id application_id BIGINT AUTO_INCREMENT NOT NULL, CHANGE JobOffer_id JobOffer_id BIGINT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL, CHANGE cv_id cv_id BIGINT NOT NULL');

        // Alter the coverletter table to update id_cover_letter and id_app
        $this->addSql('ALTER TABLE coverletter CHANGE id_cover_letter id_cover_letter BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_app id_app BIGINT NOT NULL');

        // Alter the serviceoffre table to update id_service and id_user
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL');

        // Alter the applicationservice table to update id_app and id_service
        $this->addSql('ALTER TABLE applicationservice CHANGE id_app id_app BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_service id_service BIGINT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL');

        // Alter the skilltest table to update id_test
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT AUTO_INCREMENT NOT NULL');

        // Alter the questions table to update id_question and id_test
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_test id_test BIGINT NOT NULL');

        // Alter the test_result table to update id_result and id_user
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL, CHANGE id_test id_test BIGINT NOT NULL');

        // Alter the channel table to update id_channel and user IDs
        $this->addSql('ALTER TABLE channel CHANGE id_channel id_channel BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user1 id_user1 BIGINT NOT NULL, CHANGE id_user2 id_user2 BIGINT NOT NULL');

        // Alter the message table to update id_message and user IDs
        $this->addSql('ALTER TABLE message CHANGE id_message id_message BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user_sender id_user_sender BIGINT NOT NULL, CHANGE id_channel id_channel BIGINT NOT NULL');

        // Alter the report table to update id_report and user IDs
        $this->addSql('ALTER TABLE report CHANGE id_report id_report BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user_sender id_user_sender BIGINT NOT NULL, CHANGE id_user_target id_user_target BIGINT NOT NULL');

        // Alter the certificates table
        $this->addSql('ALTER TABLE certificates CHANGE id_cv id_cv BIGINT NOT NULL');

        // Alter the languages table
        $this->addSql('ALTER TABLE languages CHANGE id_cv id_cv BIGINT NOT NULL');

        // Re-add the foreign keys after altering the tables
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C10376120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CC28BCF89 FOREIGN KEY (JobOffer_id) REFERENCES job_offer (id_offer) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coverletter ADD CONSTRAINT FK_DDD8840357CA9895 FOREIGN KEY (id_app) REFERENCES application_job (application_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_1 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_2 FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B68D307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F76120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT FK_A001537976120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv) ON DELETE CASCADE');
        
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        // Similar conditional approach for the down migration
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY IF EXISTS FK_8157AA0F6B3CA4B');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY IF EXISTS FK_B66FFE926B3CA4B');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY IF EXISTS FK_590C10376120795');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY IF EXISTS FK_288A3A4E6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356CC28BCF89');
        $this->addSql('ALTER TABLE coverletter DROP FOREIGN KEY IF EXISTS FK_DDD8840357CA9895');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY IF EXISTS FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY IF EXISTS FK_A89D71946B3CA4B');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY IF EXISTS FK_8ADC54D5535F620E');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY IF EXISTS FK_84B3C63D6B3CA4B');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY IF EXISTS FK_84B3C63D535F620E');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY IF EXISTS channel_ibfk_1');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY IF EXISTS channel_ibfk_2');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY IF EXISTS FK_B6BD307F22409C05');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY IF EXISTS FK_B68D307F7C642737');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY IF EXISTS FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY IF EXISTS FK_C42F77843B2FF936');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY IF EXISTS FK_8D26FB5F76120795');
        $this->addSql('ALTER TABLE languages DROP FOREIGN KEY IF EXISTS FK_A001537976120795');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356C6B3CA4B');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_75E7356CCFE419E2');

        // Reverse the changes (reverting AUTO_INCREMENT and NOT NULL)
        $this->addSql('ALTER TABLE app_user CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE profile CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user INT NOT NULL, CHANGE id_cv id_cv INT NOT NULL');
        $this->addSql('ALTER TABLE experience CHANGE id_cv id_cv INT NOT NULL, CHANGE id_experience id_experience INT NOT NULL');
        $this->addSql('ALTER TABLE job_offer CHANGE id_offer id_offer INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE application_job CHANGE application_id application_id INT NOT NULL, CHANGE JobOffer_id JobOffer_id INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE cv_id cv_id INT NOT NULL');
        $this->addSql('ALTER TABLE coverletter CHANGE id_cover_letter id_cover_letter INT NOT NULL, CHANGE id_app id_app INT NOT NULL');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE applicationservice CHANGE id_app id_app INT NOT NULL, CHANGE id_service id_service INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test INT NOT NULL');
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question INT NOT NULL, CHANGE id_test id_test INT NOT NULL');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE id_test id_test INT NOT NULL');
        $this->addSql('ALTER TABLE channel CHANGE id_channel id_channel INT NOT NULL, CHANGE id_user1 id_user1 INT NOT NULL, CHANGE id_user2 id_user2 INT NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE id_message id_message INT NOT NULL, CHANGE id_user_sender id_user_sender INT NOT NULL, CHANGE id_channel id_channel INT NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE id_report id_report INT NOT NULL, CHANGE id_user_sender id_user_sender INT NOT NULL, CHANGE id_user_target id_user_target INT NOT NULL');
        $this->addSql('ALTER TABLE certificates CHANGE id_cv id_cv INT NOT NULL');
        $this->addSql('ALTER TABLE languages CHANGE id_cv id_cv INT NOT NULL');

        // Re-create the foreign keys after reverting the fields
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C10376120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CC28BCF89 FOREIGN KEY (JobOffer_id) REFERENCES job_offer (id_offer) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coverletter ADD CONSTRAINT FK_DDD8840357CA9895 FOREIGN KEY (id_app) REFERENCES application_job (application_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_1 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_2 FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B68D307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F76120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT FK_A001537976120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv) ON DELETE CASCADE');
        
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}

        

