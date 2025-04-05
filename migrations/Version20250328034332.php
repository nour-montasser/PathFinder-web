<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328034332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ApplicationJob CHANGE JobOffer_id JobOffer_id BIGINT DEFAULT NULL, CHANGE id_user id_user BIGINT DEFAULT NULL, CHANGE cv_id cv_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE ApplicationJob ADD CONSTRAINT FK_75E7356C3481D195 FOREIGN KEY (JobOffer_id) REFERENCES JobOffer (id_offer)');
        $this->addSql('ALTER TABLE ApplicationJob ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user)');
        $this->addSql('ALTER TABLE ApplicationJob ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv)');
        $this->addSql('CREATE INDEX IDX_75E7356C3481D195 ON ApplicationJob (JobOffer_id)');
        $this->addSql('CREATE INDEX IDX_75E7356C6B3CA4B ON ApplicationJob (id_user)');
        $this->addSql('CREATE INDEX IDX_75E7356CCFE419E2 ON ApplicationJob (cv_id)');
        $this->addSql('ALTER TABLE applicationservice CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user)');
        $this->addSql('CREATE INDEX IDX_A89D71946B3CA4B ON applicationservice (id_user)');
        $this->addSql('ALTER TABLE certificates CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F76120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8D26FB5F76120795 ON certificates (id_cv)');
        $this->addSql('ALTER TABLE channel CHANGE rating rating INT NOT NULL');
        $this->addSql('ALTER TABLE coverletter CHANGE id_app id_app BIGINT DEFAULT NULL, CHANGE content content TEXT NOT NULL');
        $this->addSql('ALTER TABLE coverletter ADD CONSTRAINT FK_DDD8840357CA9895 FOREIGN KEY (id_app) REFERENCES ApplicationJob (application_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DDD8840357CA9895 ON coverletter (id_app)');
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B66FFE926B3CA4B ON cv (id_user)');
        $this->addSql('ALTER TABLE experience CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C10376120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_590C10376120795 ON experience (id_cv)');
        $this->addSql('ALTER TABLE JobOffer CHANGE id_user id_user BIGINT DEFAULT NULL, CHANGE description description TEXT NOT NULL');
        $this->addSql('ALTER TABLE JobOffer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_288A3A4E6B3CA4B ON JobOffer (id_user)');
        $this->addSql('ALTER TABLE languages CHANGE id_cv id_cv BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE languages ADD CONSTRAINT FK_A0D1537976120795 FOREIGN KEY (id_cv) REFERENCES cv (id_cv) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A0D1537976120795 ON languages (id_cv)');
        $this->addSql('ALTER TABLE message CHANGE content content TEXT NOT NULL, CHANGE id_user_sender id_user_sender BIGINT DEFAULT NULL, CHANGE media media VARCHAR(255) DEFAULT NULL, CHANGE id_channel id_channel BIGINT DEFAULT NULL, CHANGE timesent time_sent DATETIME NOT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7C642737 FOREIGN KEY (id_channel) REFERENCES channel (id_channel) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B6BD307F22409C05 ON message (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_B6BD307F7C642737 ON message (id_channel)');
        $this->addSql('ALTER TABLE profile CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE bio bio TEXT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE questions CHANGE id_test id_test BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D5535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8ADC54D5535F620E ON questions (id_test)');
        $this->addSql('ALTER TABLE report CHANGE id_user_sender id_user_sender BIGINT DEFAULT NULL, CHANGE id_user_target id_user_target BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES AppUser (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C42F778422409C05 ON report (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_C42F77843B2FF936 ON report (id_user_target)');
        $this->addSql('ALTER TABLE skilltest CHANGE id_JobOffer id_JobOffer BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE skilltest ADD CONSTRAINT FK_6647AA18A8BF8669 FOREIGN KEY (id_JobOffer) REFERENCES JobOffer (id_offer)');
        $this->addSql('CREATE INDEX IDX_6647AA18A8BF8669 ON skilltest (id_JobOffer)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES AppUser (id_user)');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D535F620E FOREIGN KEY (id_test) REFERENCES skilltest (id_test)');
        $this->addSql('CREATE INDEX IDX_84B3C63D6B3CA4B ON test_result (id_user)');
        $this->addSql('CREATE INDEX IDX_84B3C63D535F620E ON test_result (id_test)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71946B3CA4B');
        $this->addSql('DROP INDEX IDX_A89D71946B3CA4B ON applicationservice');
        $this->addSql('ALTER TABLE applicationservice CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE ApplicationJob DROP FOREIGN KEY FK_75E7356C3481D195');
        $this->addSql('ALTER TABLE ApplicationJob DROP FOREIGN KEY FK_75E7356C6B3CA4B');
        $this->addSql('ALTER TABLE ApplicationJob DROP FOREIGN KEY FK_75E7356CCFE419E2');
        $this->addSql('DROP INDEX IDX_75E7356C3481D195 ON ApplicationJob');
        $this->addSql('DROP INDEX IDX_75E7356C6B3CA4B ON ApplicationJob');
        $this->addSql('DROP INDEX IDX_75E7356CCFE419E2 ON ApplicationJob');
        $this->addSql('ALTER TABLE ApplicationJob CHANGE JobOffer_id JobOffer_id BIGINT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL, CHANGE cv_id cv_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F76120795');
        $this->addSql('DROP INDEX IDX_8D26FB5F76120795 ON certificates');
        $this->addSql('ALTER TABLE certificates CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE channel CHANGE rating rating BIGINT NOT NULL');
        $this->addSql('ALTER TABLE coverletter DROP FOREIGN KEY FK_DDD8840357CA9895');
        $this->addSql('DROP INDEX UNIQ_DDD8840357CA9895 ON coverletter');
        $this->addSql('ALTER TABLE coverletter CHANGE id_app id_app BIGINT NOT NULL, CHANGE content content VARCHAR(5000) NOT NULL');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE926B3CA4B');
        $this->addSql('DROP INDEX IDX_B66FFE926B3CA4B ON cv');
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C10376120795');
        $this->addSql('DROP INDEX IDX_590C10376120795 ON experience');
        $this->addSql('ALTER TABLE experience CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE JobOffer DROP FOREIGN KEY FK_288A3A4E6B3CA4B');
        $this->addSql('DROP INDEX IDX_288A3A4E6B3CA4B ON JobOffer');
        $this->addSql('ALTER TABLE JobOffer CHANGE id_user id_user BIGINT NOT NULL, CHANGE description description VARCHAR(1000) NOT NULL');
        $this->addSql('ALTER TABLE languages DROP FOREIGN KEY FK_A0D1537976120795');
        $this->addSql('DROP INDEX IDX_A0D1537976120795 ON languages');
        $this->addSql('ALTER TABLE languages CHANGE id_cv id_cv BIGINT NOT NULL');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F22409C05');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7C642737');
        $this->addSql('DROP INDEX IDX_B6BD307F22409C05 ON message');
        $this->addSql('DROP INDEX IDX_B6BD307F7C642737 ON message');
        $this->addSql('ALTER TABLE message CHANGE id_user_sender id_user_sender BIGINT NOT NULL, CHANGE id_channel id_channel BIGINT NOT NULL, CHANGE content content VARCHAR(500) NOT NULL, CHANGE media media VARCHAR(255) NOT NULL, CHANGE time_sent timesent DATETIME NOT NULL');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F6B3CA4B');
        $this->addSql('ALTER TABLE profile CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE bio bio VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D5535F620E');
        $this->addSql('DROP INDEX IDX_8ADC54D5535F620E ON questions');
        $this->addSql('ALTER TABLE questions CHANGE id_test id_test BIGINT NOT NULL');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843B2FF936');
        $this->addSql('DROP INDEX IDX_C42F778422409C05 ON report');
        $this->addSql('DROP INDEX IDX_C42F77843B2FF936 ON report');
        $this->addSql('ALTER TABLE report CHANGE id_user_sender id_user_sender BIGINT NOT NULL, CHANGE id_user_target id_user_target BIGINT NOT NULL');
        $this->addSql('ALTER TABLE skilltest DROP FOREIGN KEY FK_6647AA18A8BF8669');
        $this->addSql('DROP INDEX IDX_6647AA18A8BF8669 ON skilltest');
        $this->addSql('ALTER TABLE skilltest CHANGE id_JobOffer id_JobOffer BIGINT NOT NULL');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D535F620E');
        $this->addSql('DROP INDEX IDX_84B3C63D6B3CA4B ON test_result');
        $this->addSql('DROP INDEX IDX_84B3C63D535F620E ON test_result');
    }
}
