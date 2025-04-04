<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329031419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job CHANGE application_id application_id BIGINT NOT NULL, CHANGE idUser id_user BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('CREATE INDEX IDX_75E7356C6B3CA4B ON application_job (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356C6B3CA4B');
        $this->addSql('DROP INDEX IDX_75E7356C6B3CA4B ON application_job');
        $this->addSql('ALTER TABLE application_job CHANGE application_id application_id BIGINT AUTO_INCREMENT NOT NULL, CHANGE id_user idUser BIGINT DEFAULT NULL');
    }
}
