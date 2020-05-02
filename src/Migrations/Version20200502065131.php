<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200502065131 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить таблицы для хранения скалярных, целочисленных и вещественных значений и таблицу для хранения возможных значений';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<'SQL'
CREATE TABLE scalar_value (
    id SERIAL,
    value VARCHAR(255) NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE int_value (
    id SERIAL,
    lower BIGINT NOT NULL,
    upper BIGINT NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE real_value (
    id SERIAL,
    lower DOUBLE PRECISION NOT NULL,
    lower_is_inclusive TINYINT(1) NOT NULL,
    upper DOUBLE PRECISION NOT NULL,
    upper_is_inclusive TINYINT(1) NOT NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE feature_possible_value (
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

        $this->addSql('DROP TABLE feature_possible_value');
        $this->addSql('DROP TABLE real_value');
        $this->addSql('DROP TABLE int_value');
        $this->addSql('DROP TABLE scalar_value');
    }
}
