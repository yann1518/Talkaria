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

            $payload['email'] = $user->getUserIdentifier();
            unset($payload['username']);

            $event->setData($payload);
        }
    }


