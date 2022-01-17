<?php

namespace Aldemeery\Shieldify;

use Illuminate\Contracts\Encryption\DecryptException;

class Key
{
    /**
     * User ID.
     *
     * @var mixed
     */
    private $id;

    /**
     * Timestamp when this key expires.
     *
     * @var integer
     */
    private $expiresAt;

    /**
     * Constructor.
     *
     * @param mixed $id
     * @param int   $expiresAt
     */
    public function __construct($id, $expiresAt)
    {
        $this->id = $id;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the id.
     *
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Determine whether this key has expired.
     *
     * @return bool
     */
    public function hasExpired()
    {
        return time() > $this->expiresAt;
    }

    /**
     * Create a new key instace from a given string.
     *
     * @param string|null $key
     *
     * @return \Aldemeery\Shieldify\Key
     */
    public static function from(?string $key)
    {
        try {
            $data = json_decode(decrypt((string) $key), true);
        } catch (DecryptException $e) {
            $data = [];
        }

        $id = $data['id'] ?? null;
        $expiresAt = $data['expires_at'] ?? 0;

        return new static($id, $expiresAt);
    }

    /**
     * Convert this key to a string.
     *
     * @return string
     */
    public function toString()
    {
        return encrypt(json_encode([
            'id' => $this->id,
            'expires_at' => $this->expiresAt,
        ]));
    }

    /**
     * Get the string representation of this key.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
