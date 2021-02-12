#!/bin/sh

whoami
su - postgres -c "vacuumdb -f -v integrado"
