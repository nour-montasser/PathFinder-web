<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326021549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE App_user CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE coverletter ADD PRIMARY KEY (id_cover_letter)');
        $this->addSql('ALTER TABLE cv CHANGE id_cv id_cv BIGINT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE user_title user_title VARCHAR(255) NOT NULL, CHANGE introduction introduction VARCHAR(500) NOT NULL, CHANGE date_creation date_creation DATETIME NOT NULL, CHANGE skills skills VARCHAR(255) NOT NULL, CHANGE last_viewed last_viewed DATETIME NOT NULL, CHANGE favorite favorite TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE experience CHANGE id_experience id_experience BIGINT NOT NULL, CHANGE TYPE type VARCHAR(255) NOT NULL, CHANGE POSITION position VARCHAR(255) NOT NULL, CHANGE location_name location_name VARCHAR(255) NOT NULL, CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME NOT NULL, CHANGE description description VARCHAR(500) NOT NULL');
        $this->addSql('DROP INDEX id_user ON JobOffer');
        $this->addSql('ALTER TABLE JobOffer ADD date_posted DATETIME NOT NULL, DROP datePosted, CHANGE id_offer id_offer BIGINT NOT NULL, CHANGE description description VARCHAR(1000) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE number_of_spots number_of_spots INT NOT NULL, CHANGE required_education required_education VARCHAR(255) NOT NULL, CHANGE required_experience required_experience VARCHAR(255) NOT NULL, CHANGE skills skills VARCHAR(255) NOT NULL, CHANGE field field VARCHAR(255) NOT NULL, CHANGE address address VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE languages CHANGE id_language id_language BIGINT NOT NULL');
        $this->addSql('DROP INDEX id_user_sender ON message');
        $this->addSql('DROP INDEX id_channel ON message');
        $this->addSql('ALTER TABLE message CHANGE id_message id_message BIGINT NOT NULL, CHANGE media media VARCHAR(255) NOT NULL, CHANGE timesent timesent DATETIME NOT NULL');
        $this->addSql('ALTER TABLE profile CHANGE address address VARCHAR(255) NOT NULL, CHANGE birthday birthday DATE NOT NULL, CHANGE phone phone VARCHAR(20) NOT NULL, CHANGE current_occupation current_occupation VARCHAR(255) NOT NULL, CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE bio bio VARCHAR(500) NOT NULL');
        $this->addSql('DROP INDEX id_test ON questions');
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question BIGINT NOT NULL, CHANGE question question VARCHAR(1000) NOT NULL, CHANGE responses responses VARCHAR(1000) NOT NULL, CHANGE correct_response correct_response VARCHAR(1000) NOT NULL, CHANGE score score INT NOT NULL');
        $this->addSql('DROP INDEX id_user_sender ON report');
        $this->addSql('DROP INDEX id_user_target ON report');
        $this->addSql('ALTER TABLE report CHANGE id_report id_report BIGINT NOT NULL, CHANGE description description VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE date_posted date_posted DATETIME NOT NULL, CHANGE experience_level experience_level VARCHAR(50) NOT NULL, CHANGE duration duration VARCHAR(50) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('DROP INDEX id_JobOffer ON skilltest');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(1000) NOT NULL, CHANGE duration duration BIGINT NOT NULL, CHANGE score_required score_required BIGINT NOT NULL');
        $this->addSql('DROP INDEX id_user ON test_result');
        $this->addSql('DROP INDEX id_test ON test_result');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT NOT NULL, CHANGE result result DOUBLE PRECISION NOT NULL, CHANGE date date DATETIME NOT NULL, CHANGE status status TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE App_user CHANGE name name VARCHAR(200) NOT NULL');
        $this->addSql('DROP INDEX `primary` ON coverletter');
        $this->addSql('ALTER TABLE cv CHANGE id_cv id_cv BIGINT AUTO_INCREMENT NOT NULL, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE user_title user_title VARCHAR(255) DEFAULT NULL, CHANGE introduction introduction VARCHAR(500) DEFAULT NULL, CHANGE date_creation date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE skills skills VARCHAR(255) DEFAULT NULL, CHANGE last_viewed last_viewed DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE favorite favorite TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE experience CHANGE id_experience id_experience BIGINT AUTO_INCREMENT NOT NULL, CHANGE type TYPE VARCHAR(255) DEFAULT NULL, CHANGE position POSITION VARCHAR(255) DEFAULT NULL, CHANGE location_name location_name VARCHAR(255) DEFAULT NULL, CHANGE start_date start_date DATETIME DEFAULT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL, CHANGE description description VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE JobOffer ADD datePosted DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP date_posted, CHANGE id_offer id_offer BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE number_of_spots number_of_spots INT DEFAULT NULL, CHANGE required_education required_education VARCHAR(255) DEFAULT NULL, CHANGE required_experience required_experience VARCHAR(255) DEFAULT NULL, CHANGE skills skills VARCHAR(255) DEFAULT NULL, CHANGE field field VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX id_user ON JobOffer (id_user)');
        $this->addSql('ALTER TABLE languages CHANGE id_language id_language BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE id_message id_message BIGINT AUTO_INCREMENT NOT NULL, CHANGE media media VARCHAR(255) DEFAULT NULL, CHANGE timesent timesent DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX id_user_sender ON message (id_user_sender)');
        $this->addSql('CREATE INDEX id_channel ON message (id_channel)');
        $this->addSql('ALTER TABLE profile CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE birthday birthday DATE DEFAULT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE current_occupation current_occupation VARCHAR(255) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE bio bio VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE questions CHANGE id_question id_question BIGINT AUTO_INCREMENT NOT NULL, CHANGE question question VARCHAR(1000) DEFAULT NULL, CHANGE responses responses VARCHAR(1000) DEFAULT NULL, CHANGE correct_response correct_response VARCHAR(1000) DEFAULT NULL, CHANGE score score INT DEFAULT NULL');
        $this->addSql('CREATE INDEX id_test ON questions (id_test)');
        $this->addSql('ALTER TABLE report CHANGE id_report id_report BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(500) DEFAULT NULL');
        $this->addSql('CREATE INDEX id_user_sender ON report (id_user_sender)');
        $this->addSql('CREATE INDEX id_user_target ON report (id_user_target)');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE date_posted date_posted DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE experience_level experience_level VARCHAR(50) DEFAULT NULL, CHANGE duration duration VARCHAR(50) DEFAULT NULL, CHANGE status status VARCHAR(20) DEFAULT \'Open\'');
        $this->addSql('ALTER TABLE skilltest CHANGE id_test id_test BIGINT AUTO_INCREMENT NOT NULL, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL, CHANGE duration duration BIGINT DEFAULT NULL, CHANGE score_required score_required BIGINT DEFAULT NULL');
        $this->addSql('CREATE INDEX id_JobOffer ON skilltest (id_JobOffer)');
        $this->addSql('ALTER TABLE test_result CHANGE id_result id_result BIGINT AUTO_INCREMENT NOT NULL, CHANGE result result DOUBLE PRECISION DEFAULT NULL, CHANGE date date DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE status status TINYINT(1) DEFAULT NULL');
        $this->addSql('CREATE INDEX id_user ON test_result (id_user)');
        $this->addSql('CREATE INDEX id_test ON test_result (id_test)');
    }
}
