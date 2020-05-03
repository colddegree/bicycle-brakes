<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200503104305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить таблицу для хранения нормальных значений признаков';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<'SQL'
CREATE TABLE feature_normal_value (
    id SERIAL,
    feature_id BIGINT UNSIGNED NOT NULL,
    scalar_value_id BIGINT UNSIGNED DEFAULT NULL,
    int_value_id BIGINT UNSIGNED DEFAULT NULL,
    real_value_id BIGINT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (feature_id) REFERENCES feature (id),
    FOREIGN KEY (scalar_value_id) REFERENCES scalar_value (id),
    FOREIGN KEY (int_value_id) REFERENCES int_value (id),
    FOREIGN KEY (real_value_id) REFERENCES real_value (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE feature_normal_value');
    }
}
