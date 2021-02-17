<?php declare(strict_types=1);

namespace Tests\Domain\Authentication\Entity;

use App\Domain\Authentication\Entity\User;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function provideTokenWithUAAndIPExpectations(): array
    {
        $dateTimeInFuture = new \DateTimeImmutable('+5 days');

        return [
            'UA Only | Valid UA' => [
                'data' => [
                    'allowedUserAgents'  => ['Chrome on Linux'],
                    'allowedIpAddresses' => []
                ],
                'inputUA'        => 'Chrome on Linux',
                'inputIP'        => '172.0.0.1',
                'expirationDate' => $dateTimeInFuture,
                'expectation'    => true
            ],

            'UA Only | Invalid UA in header' => [
                'data' => [
                    'allowedUserAgents'  => ['Chrome on Linux'],
                    'allowedIpAddresses' => []
                ],
                'inputUA'        => 'Internet Explorer on Windows',
                'inputIP'        => '172.0.0.1',
                'expirationDate' => $dateTimeInFuture,
                'expectation'    => false
            ],

            'UA Only | No User-Agent header passed -> FAIL' => [
                'data' => [
                    'allowedUserAgents'  => ['Chrome on Linux'],
                    'allowedIpAddresses' => []
                ],
                'inputUA'        => '',
                'inputIP'        => '172.0.0.1',
                'expirationDate' => $dateTimeInFuture,
                'expectation'    => false
            ],

            'UA + IP address | VALID' => [
                'data' => [
                    'allowedUserAgents'  => ['Chrome on Linux'],
                    'allowedIpAddresses' => ['172.0.0.1', '172.0.0.2']
                ],
                'inputUA'        => 'Chrome on Linux',
                'inputIP'        => '172.0.0.1',
                'expirationDate' => $dateTimeInFuture,
                'expectation'    => true
            ],

            'UA + IP address | INVALID IP ADDRESS' => [
                'data' => [
                    'allowedUserAgents'  => ['Chrome on Linux'],
                    'allowedIpAddresses' => ['172.0.0.2']
                ],
                'inputUA'        => 'Chrome on Linux',
                'inputIP'        => '192.16.1.15',
                'expirationDate' => $dateTimeInFuture,
                'expectation'    => false
            ],

            'Token already expired | FAILURE' => [
                'data' => [
                    'allowedUserAgents'  => ['Valid UA'],
                    'allowedIpAddresses' => ['1.1.1.1']
                ],
                'inputUA'        => 'Valid UA',
                'inputIP'        => '1.1.1.1',
                'expirationDate' => new \DateTimeImmutable('-10 days'),
                'expectation'    => false
            ]
        ];
    }

    /**
     * @dataProvider provideTokenWithUAAndIPExpectations
     *
     * @param array     $data
     * @param string    $inputUA
     * @param string    $inputIP
     * @param \DateTime $expirationDate
     * @param bool      $expectation
     *
     * @throws \Exception
     */
    public function testIsValid(array $data, string $inputUA, string $inputIP, \DateTimeImmutable $expirationDate, bool $expectation): void
    {
        $token = new User();
        $token->setData($data);
        $token->setExpirationDate($expirationDate);

        $this->assertSame($expectation, $token->isValid($inputUA, $inputIP));
    }
}
