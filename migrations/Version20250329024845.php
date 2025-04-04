<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329024845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CFE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_75E7356CFE6E88D7 ON application_job (id_user)');
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D71946B3CA4B');
        $this->addSql('DROP INDEX IDX_A89D71946B3CA4B ON applicationservice');
        $this->addSql('ALTER TABLE applicationservice CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D7194FE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_A89D7194FE6E88D7 ON applicationservice (id_user)');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY channel_ibfk_1');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY channel_ibfk_2');
        $this->addSql('DROP INDEX IDX_A2F98E4762D4C465 ON channel');
        $this->addSql('DROP INDEX IDX_A2F98E47FBDD95DF ON channel');
        $this->addSql('ALTER TABLE channel ADD id_user1 BIGINT DEFAULT NULL, ADD id_user2 BIGINT DEFAULT NULL, DROP id_user1, DROP id_user2');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E479B95C648 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E4729C97F2 FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A2F98E479B95C648 ON channel (id_user1)');
        $this->addSql('CREATE INDEX IDX_A2F98E4729C97F2 ON channel (id_user2)');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE926B3CA4B');
        $this->addSql('DROP INDEX IDX_B66FFE926B3CA4B ON cv');
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE92FE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B66FFE92FE6E88D7 ON cv (id_user)');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E6B3CA4B');
        $this->addSql('DROP INDEX IDX_288A3A4E6B3CA4B ON job_offer');
        $this->addSql('ALTER TABLE job_offer CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4EFE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_288A3A4EFE6E88D7 ON job_offer (id_user)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F22409C05');
        $this->addSql('DROP INDEX IDX_B6BD307F22409C05 ON message');
        $this->addSql('ALTER TABLE message CHANGE id_user_sender id_user_sender BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F7A0FAE8A FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B6BD307F7A0FAE8A ON message (id_user_sender)');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0F6B3CA4B');
        $this->addSql('DROP INDEX `primary` ON profile');
        $this->addSql('ALTER TABLE profile CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FFE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD PRIMARY KEY (id_user)');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F778422409C05');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843B2FF936');
        $this->addSql('DROP INDEX IDX_C42F778422409C05 ON report');
        $this->addSql('DROP INDEX IDX_C42F77843B2FF936 ON report');
        $this->addSql('ALTER TABLE report ADD id_user_sender BIGINT DEFAULT NULL, ADD id_user_target BIGINT DEFAULT NULL, DROP id_user_sender, DROP id_user_target');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77847A0FAE8A FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846360CBB9 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C42F77847A0FAE8A ON report (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_C42F77846360CBB9 ON report (id_user_target)');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63D6B3CA4B');
        $this->addSql('DROP INDEX IDX_84B3C63D6B3CA4B ON test_result');
        $this->addSql('ALTER TABLE test_result ADD id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63DFE6E88D7 FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_84B3C63DFE6E88D7 ON test_result (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_A89D7194FE6E88D7');
        $this->addSql('DROP INDEX IDX_A89D7194FE6E88D7 ON applicationservice');
        $this->addSql('ALTER TABLE applicationservice CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71946B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_A89D71946B3CA4B ON applicationservice (id_user)');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CFE6E88D7');
        $this->addSql('DROP INDEX IDX_75E7356CFE6E88D7 ON application_job');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E479B95C648');
        $this->addSql('ALTER TABLE channel DROP FOREIGN KEY FK_A2F98E4729C97F2');
        $this->addSql('DROP INDEX IDX_A2F98E479B95C648 ON channel');
        $this->addSql('DROP INDEX IDX_A2F98E4729C97F2 ON channel');
        $this->addSql('ALTER TABLE channel ADD id_user1 BIGINT DEFAULT NULL, ADD id_user2 BIGINT DEFAULT NULL, DROP id_user1, DROP id_user2');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_1 FOREIGN KEY (id_user1) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT channel_ibfk_2 FOREIGN KEY (id_user2) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A2F98E4762D4C465 ON channel (id_user1)');
        $this->addSql('CREATE INDEX IDX_A2F98E47FBDD95DF ON channel (id_user2)');
        $this->addSql('ALTER TABLE cv DROP FOREIGN KEY FK_B66FFE92FE6E88D7');
        $this->addSql('DROP INDEX IDX_B66FFE92FE6E88D7 ON cv');
        $this->addSql('ALTER TABLE cv CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE cv ADD CONSTRAINT FK_B66FFE926B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B66FFE926B3CA4B ON cv (id_user)');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4EFE6E88D7');
        $this->addSql('DROP INDEX IDX_288A3A4EFE6E88D7 ON job_offer');
        $this->addSql('ALTER TABLE job_offer CHANGE id_user id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_288A3A4E6B3CA4B ON job_offer (id_user)');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F7A0FAE8A');
        $this->addSql('DROP INDEX IDX_B6BD307F7A0FAE8A ON message');
        $this->addSql('ALTER TABLE message CHANGE id_user_sender id_user_sender BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F22409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B6BD307F22409C05 ON message (id_user_sender)');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FFE6E88D7');
        $this->addSql('DROP INDEX `PRIMARY` ON profile');
        $this->addSql('ALTER TABLE profile CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile ADD PRIMARY KEY (id_user)');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77847A0FAE8A');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846360CBB9');
        $this->addSql('DROP INDEX IDX_C42F77847A0FAE8A ON report');
        $this->addSql('DROP INDEX IDX_C42F77846360CBB9 ON report');
        $this->addSql('ALTER TABLE report ADD id_user_sender BIGINT DEFAULT NULL, ADD id_user_target BIGINT DEFAULT NULL, DROP id_user_sender, DROP id_user_target');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778422409C05 FOREIGN KEY (id_user_sender) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843B2FF936 FOREIGN KEY (id_user_target) REFERENCES app_user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C42F778422409C05 ON report (id_user_sender)');
        $this->addSql('CREATE INDEX IDX_C42F77843B2FF936 ON report (id_user_target)');
        $this->addSql('ALTER TABLE test_result DROP FOREIGN KEY FK_84B3C63DFE6E88D7');
        $this->addSql('DROP INDEX IDX_84B3C63DFE6E88D7 ON test_result');
        $this->addSql('ALTER TABLE test_result DROP id_user');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_84B3C63D6B3CA4B ON test_result (id_user)');
    }
}
