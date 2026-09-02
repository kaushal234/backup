# Provisioning

## Prerequisites for running provisioning locally

- ansible >= 2.8
- ssh config : on each of the relevant hostnames below, `ssh ansible@<hostname>` should work without password authentication.

## Secrets

Some template files contain secrets; they are encrypted with `ansible-vault`.
The vault password will be prompted when attempting to run playbooks using secrets, or use the option --vault-pass-file .vault
The vault password is stored on passbolt.

Some useful commands:

- `ansible-vault view <file>`: after prompting for password, shows the content of an encrypted file.
- `ansible-vault edit <file>`: after prompting for password, opens $EDITOR to allow editing the content of an encrypted file.
- `ansible-vault rekey <file>`: after prompting for password, allows to change the password of an encrypted file.

See more on the [ansible-vault doc](https://docs.ansible.com/ansible/latest/user_guide/vault.html).
