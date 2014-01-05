<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\ResettingController as BaseResettingController;
use FOS\UserBundle\Model\UserInterface;

class ResettingController extends BaseResettingController
{
    public function checkEmailAction(Request $request)
    {
        $userDiscriminator = $this->container->get('rollerworks_multi_user.user_discriminator');
        /** @var \Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface $userDiscriminator */
        $email = $request->query->get('email');

        if (empty($email)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->container->get('router')->generate($userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_resetting_request'));
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:checkEmail.html.'.$this->getEngine(), array(
            'email' => $email,
        ));
    }

    public function sendEmailAction(Request $request)
    {
        $userDiscriminator = $this->container->get('rollerworks_multi_user.user_discriminator');
        /** @var \Rollerworks\Bundle\MultiUserBundle\Model\UserDiscriminatorInterface $userDiscriminator */

        $username = $request->request->get('username');
        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:request.html.'.$this->getEngine(), array('invalid_username' => $username));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine());
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new RedirectResponse($this->container->get('router')->generate(
            $userDiscriminator->getCurrentUserConfig()->getRoutePrefix() . '_resetting_check_email',
            array('email' => $this->getObfuscatedEmail($user))
        ));
    }
}
