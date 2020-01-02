<?php declare(strict_types=1);

namespace App\Domain\Replication\Factory;

use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Entity\ReplicationClient;
use App\Domain\Replication\Security\ReplicationContext;
use App\Domain\Replication\Service\CryptoService;
use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;
use App\Domain\Roles;

class SecurityContextFactory
{
    private $crypto;

    public function __construct(CryptoService $crypto)
    {
        $this->crypto = $crypto;
    }

    public function create(Token $token): ReplicationContext
    {
        $client = new ReplicationClient(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_REPLICATION_ENC_METHOD, ''))
        );

        return new ReplicationContext(
            $token->hasRole(Roles::ROLE_STREAMING_REPLICATION),
            $token->hasRole(Roles::ROLE_READ_REPLICATION_SECRETS),
            $client
        );
    }

    private function getEncryptionKey(Token $token): string
    {
        $key = $token->getDataField(Token::FIELD_REPLICATION_ENC_KEY, '');

        if ($key) {
            return $this->crypto->decode($key);
        }

        return '';
    }

    public function createShellContext(Token $token): ReplicationContext
    {
        $client = new ReplicationClient(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_REPLICATION_ENC_METHOD, ''))
        );

        return new ReplicationContext(true, true, $client);
    }
}
