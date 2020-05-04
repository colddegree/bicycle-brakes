<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200504100738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить таблицу связку для хранения клинических картин неисправностей';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<'SQL'
CREATE TABLE malfunction_clinical_picture (
    malfunction_id BIGINT UNSIGNED NOT NULL,
    feature_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (malfunction_id, feature_id),
    FOREIGN KEY (malfunction_id) REFERENCES malfunction (id),
    FOREIGN KEY (feature_id) REFERENCES feature (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE malfunction_clinical_picture');
    }
}
