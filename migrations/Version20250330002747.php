<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330002747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CC28BCF89 FOREIGN KEY (JobOffer_id) REFERENCES job_offer (idOffer)');
        $this->addSql('ALTER TABLE skilltest DROP FOREIGN KEY FK_6647AA189B5320A3');
        $this->addSql('ALTER TABLE skilltest ADD CONSTRAINT FK_6647AA189B5320A3 FOREIGN KEY (id_JobOffer) REFERENCES job_offer (idOffer)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_75E7356CC28BCF89');
        $this->addSql('ALTER TABLE skilltest DROP FOREIGN KEY FK_6647AA189B5320A3');
        $this->addSql('ALTER TABLE skilltest ADD CONSTRAINT FK_6647AA189B5320A3 FOREIGN KEY (id_JobOffer) REFERENCES job_offer (id_offer)');
    }
}
