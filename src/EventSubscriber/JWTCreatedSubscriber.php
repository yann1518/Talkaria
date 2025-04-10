<?php

    namespace App\EventSubscriber;

    use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class JWTCreatedSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return [
                JWTCreatedEvent::class => 'onJWTCreated',
            ];
        }

        public function onJWTCreated(JWTCreatedEvent $event): void
        {
            $user = $event->getUser();

            $payload = $event->getData();

            // Remplace "username" par "email" dans le payload
            $payload['email'] = $user->getUserIdentifier(); // L'email du user
            unset($payload['username']); // On peut enlever username du payload

            $event->setData($payload);
        }
    }

