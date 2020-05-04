<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200504134936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Добавить кучу таблиц для хранения значений признаков неисправностей';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE malfunction_feature_value_bind (id SERIAL, malfunction_id BIGINT UNSIGNED NOT NULL, feature_id BIGINT UNSIGNED NOT NULL, INDEX IDX_A5470F5018229D0A (malfunction_id), INDEX IDX_A5470F5060E4B879 (feature_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE malfunction_feature_value_bind_scalar_value (malfunction_feature_value_bind_id BIGINT UNSIGNED NOT NULL, scalar_value_id BIGINT UNSIGNED NOT NULL, INDEX IDX_B5390BD065DD23D9 (malfunction_feature_value_bind_id), INDEX IDX_B5390BD09C7CCDA3 (scalar_value_id), PRIMARY KEY(malfunction_feature_value_bind_id, scalar_value_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE malfunction_feature_value_bind_int_value (malfunction_feature_value_bind_id BIGINT UNSIGNED NOT NULL, int_value_id BIGINT UNSIGNED NOT NULL, INDEX IDX_F0F8AF5265DD23D9 (malfunction_feature_value_bind_id), INDEX IDX_F0F8AF522C7E246 (int_value_id), PRIMARY KEY(malfunction_feature_value_bind_id, int_value_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE malfunction_feature_value_bind_real_value (malfunction_feature_value_bind_id BIGINT UNSIGNED NOT NULL, real_value_id BIGINT UNSIGNED NOT NULL, INDEX IDX_C3B89B7065DD23D9 (malfunction_feature_value_bind_id), INDEX IDX_C3B89B70E45D33F (real_value_id), PRIMARY KEY(malfunction_feature_value_bind_id, real_value_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind ADD CONSTRAINT FK_A5470F5018229D0A FOREIGN KEY (malfunction_id) REFERENCES malfunction (id)');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind ADD CONSTRAINT FK_A5470F5060E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id)');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_scalar_value ADD CONSTRAINT FK_B5390BD065DD23D9 FOREIGN KEY (malfunction_feature_value_bind_id) REFERENCES malfunction_feature_value_bind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_scalar_value ADD CONSTRAINT FK_B5390BD09C7CCDA3 FOREIGN KEY (scalar_value_id) REFERENCES scalar_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_int_value ADD CONSTRAINT FK_F0F8AF5265DD23D9 FOREIGN KEY (malfunction_feature_value_bind_id) REFERENCES malfunction_feature_value_bind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_int_value ADD CONSTRAINT FK_F0F8AF522C7E246 FOREIGN KEY (int_value_id) REFERENCES int_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_real_value ADD CONSTRAINT FK_C3B89B7065DD23D9 FOREIGN KEY (malfunction_feature_value_bind_id) REFERENCES malfunction_feature_value_bind (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_real_value ADD CONSTRAINT FK_C3B89B70E45D33F FOREIGN KEY (real_value_id) REFERENCES real_value (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE malfunction_feature_value_bind_scalar_value DROP FOREIGN KEY FK_B5390BD065DD23D9');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_int_value DROP FOREIGN KEY FK_F0F8AF5265DD23D9');
        $this->addSql('ALTER TABLE malfunction_feature_value_bind_real_value DROP FOREIGN KEY FK_C3B89B7065DD23D9');
        $this->addSql('DROP TABLE malfunction_feature_value_bind');
        $this->addSql('DROP TABLE malfunction_feature_value_bind_scalar_value');
        $this->addSql('DROP TABLE malfunction_feature_value_bind_int_value');
        $this->addSql('DROP TABLE malfunction_feature_value_bind_real_value');
    }
}
