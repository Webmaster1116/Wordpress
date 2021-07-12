<?php
 namespace MailPoetVendor\Symfony\Component\Validator\Constraints; if (!defined('ABSPATH')) exit; use MailPoetVendor\Symfony\Component\Validator\Constraint; use MailPoetVendor\Symfony\Component\Validator\ConstraintValidator; use MailPoetVendor\Symfony\Component\Validator\Exception\UnexpectedTypeException; class IsTrueValidator extends \MailPoetVendor\Symfony\Component\Validator\ConstraintValidator { public function validate($value, \MailPoetVendor\Symfony\Component\Validator\Constraint $constraint) { if (!$constraint instanceof \MailPoetVendor\Symfony\Component\Validator\Constraints\IsTrue) { throw new \MailPoetVendor\Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, \MailPoetVendor\Symfony\Component\Validator\Constraints\IsTrue::class); } if (null === $value) { return; } if (\true !== $value && 1 !== $value && '1' !== $value) { $this->context->buildViolation($constraint->message)->setParameter('{{ value }}', $this->formatValue($value))->setCode(\MailPoetVendor\Symfony\Component\Validator\Constraints\IsTrue::NOT_TRUE_ERROR)->addViolation(); } } } 