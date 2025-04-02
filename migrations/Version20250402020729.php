<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fixed migration to handle duplicate index names
 */
final class Version20250402020729_fixed extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes duplicate index name issues';
    }

    public function up(Schema $schema): void
    {
        // Disable foreign key checks temporarily
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');

        // First handle the application_job table changes
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_APP_JOB_JOB_OFFER');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_APP_JOB_USER');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY IF EXISTS FK_APP_JOB_CV');
        
        // Drop existing indexes if they exist
        $this->addSql('DROP INDEX IF EXISTS IDX_75E7356C3481D195 ON application_job');
        $this->addSql('DROP INDEX IF EXISTS idx_app_job_job_offer ON application_job');
        $this->addSql('DROP INDEX IF EXISTS IDX_75E7356C6B3CA4B ON application_job');
        $this->addSql('DROP INDEX IF EXISTS idx_app_job_user ON application_job');
        $this->addSql('DROP INDEX IF EXISTS IDX_75E7356CCFE419E2 ON application_job');
        $this->addSql('DROP INDEX IF EXISTS idx_app_job_cv ON application_job');
        
        // Create new indexes with unique names
        $this->addSql('CREATE INDEX IDX_APP_JOB_OFFER_NEW ON application_job (job_offer_id)');
        $this->addSql('CREATE INDEX IDX_APP_JOB_USER_NEW ON application_job (id_user)');
        $this->addSql('CREATE INDEX IDX_APP_JOB_CV_NEW ON application_job (cv_id)');
        
        // Add constraints with new names
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_APP_JOB_OFFER_NEW FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_offer)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_APP_JOB_USER_NEW FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_APP_JOB_CV_NEW FOREIGN KEY (cv_id) REFERENCES cv (id_cv)');

        // Handle serviceoffre table changes
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY IF EXISTS FK_A89D71943F0033A2');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_APPSERVICE_SERVICE FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service)');

        // Handle serviceoffre user foreign key
        $this->addSql('ALTER TABLE serviceoffre DROP FOREIGN KEY IF EXISTS FK_SERVICE_OFFRE_USER');
        $this->addSql('DROP INDEX IF EXISTS IDX_3CE042986B3CA4B ON serviceoffre');
        $this->addSql('DROP INDEX IF EXISTS idx_service_offre_user ON serviceoffre');
        $this->addSql('CREATE INDEX IDX_SERVICE_USER_NEW ON serviceoffre (id_user)');
        $this->addSql('ALTER TABLE serviceoffre ADD CONSTRAINT FK_SERVICE_USER_NEW FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');

        // Re-enable foreign key checks
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');

        // Revert application_job changes
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_APP_JOB_OFFER_NEW');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_APP_JOB_USER_NEW');
        $this->addSql('ALTER TABLE application_job DROP FOREIGN KEY FK_APP_JOB_CV_NEW');
        
        $this->addSql('DROP INDEX IDX_APP_JOB_OFFER_NEW ON application_job');
        $this->addSql('DROP INDEX IDX_APP_JOB_USER_NEW ON application_job');
        $this->addSql('DROP INDEX IDX_APP_JOB_CV_NEW ON application_job');
        
        // Recreate original indexes (if needed)
        $this->addSql('CREATE INDEX IDX_75E7356C3481D195 ON application_job (job_offer_id)');
        $this->addSql('CREATE INDEX IDX_75E7356C6B3CA4B ON application_job (id_user)');
        $this->addSql('CREATE INDEX IDX_75E7356CCFE419E2 ON application_job (cv_id)');
        
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C3481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_offer)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356C6B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user)');
        $this->addSql('ALTER TABLE application_job ADD CONSTRAINT FK_75E7356CCFE419E2 FOREIGN KEY (cv_id) REFERENCES cv (id_cv)');

        // Revert serviceoffre changes
        $this->addSql('ALTER TABLE applicationservice DROP FOREIGN KEY FK_APPSERVICE_SERVICE');
        $this->addSql('ALTER TABLE serviceoffre CHANGE id_service id_service BIGINT NOT NULL');
        $this->addSql('ALTER TABLE applicationservice ADD CONSTRAINT FK_A89D71943F0033A2 FOREIGN KEY (id_service) REFERENCES serviceoffre (id_service)');

        // Revert serviceoffre user foreign key
        $this->addSql('ALTER TABLE serviceoffre DROP FOREIGN KEY FK_SERVICE_USER_NEW');
        $this->addSql('DROP INDEX IDX_SERVICE_USER_NEW ON serviceoffre');
        $this->addSql('CREATE INDEX IDX_3CE042986B3CA4B ON serviceoffre (id_user)');
        $this->addSql('ALTER TABLE serviceoffre ADD CONSTRAINT FK_3CE042986B3CA4B FOREIGN KEY (id_user) REFERENCES app_user (id_user) ON DELETE CASCADE');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}