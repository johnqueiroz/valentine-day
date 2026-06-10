# Deploy — Oracle Cloud Always Free (grátis, sempre)

App: Laravel 13 + Inertia/Vue + **SQLite** + upload de fotos em `storage/app/public`.
Numa VM, banco e fotos ficam no disco persistente — **sem mudar código**.

> Por que VM e não PaaS grátis: o app salva fotos no disco e usa SQLite (arquivo).
> Plataformas com disco efêmero apagam isso a cada deploy. A VM resolve sem trocar o
> banco por Postgres nem mover fotos para S3.

---

## 1. Criar a VM

1. Conta em https://www.oracle.com/cloud/free/ → **Always Free**.
2. Compute → Create Instance → **Ubuntu 22.04** (shape **Ampere A1**, always free).
3. Baixe a chave SSH. Anote o **IP público**.
4. **Abrir portas** (2 lugares — esquecer o 2º é o erro mais comum):
   - Console: VCN → Security List → Ingress → liberar TCP **80** e **443** (0.0.0.0/0).
   - No host (a imagem da Oracle bloqueia por padrão):
     ```bash
     sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
     sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
     sudo netfilter-persistent save
     ```

## 2. Domínio + HTTPS grátis (DuckDNS)

1. https://www.duckdns.org → login → criar subdomínio (ex.: `meucasal`), apontar para o
   **IP público** da VM. Fica `meucasal.duckdns.org`.
2. O Caddy (passo 5) emite o certificado HTTPS automático para esse domínio.

## 3. Dependências (na VM, via SSH)

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-sqlite3 php8.3-gd \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-intl \
  git unzip

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Caddy (servidor web + HTTPS automático)
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update && sudo apt install -y caddy
```

## 4. Subir o app

```bash
sudo mkdir -p /var/www && sudo chown $USER:$USER /var/www
cd /var/www
git clone git@github.com:johnqueiroz/valentine-day.git
cd valentine-day

cp .env.production.example .env
# edite o .env: troque APP_URL para https://meucasal.duckdns.org
nano .env

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Criar o usuário admin (sem expor senha em seeder):
```bash
php artisan tinker --execute="\App\Models\User::create(['name'=>'Admin','email'=>'voce@exemplo.com','password'=>bcrypt('TROQUE_ESTA_SENHA')]);"
```
(Opcional: `php artisan db:seed --class=WrappedDemoSeeder` cria a demo em `/w/demo`.)

## 5. Permissões

php-fpm roda como `www-data` e precisa escrever no SQLite **e** na pasta `database/`
(o SQLite cria arquivos `-journal`/`-wal` ao lado):
```bash
sudo chown -R www-data:www-data storage bootstrap/cache database
sudo chmod -R ug+rw storage bootstrap/cache database
```

## 6. Caddy (web server)

```bash
sudo cp deploy/Caddyfile /etc/caddy/Caddyfile
sudo nano /etc/caddy/Caddyfile   # troque SEU-SUBDOMINIO.duckdns.org
sudo mkdir -p /var/log/caddy && sudo chown caddy:caddy /var/log/caddy
sudo systemctl reload caddy
```

Acesse **https://meucasal.duckdns.org** — cadeado verde, app no ar.

## 7. Atualizações futuras

```bash
cd /var/www/valentine-day && bash deploy/deploy.sh
```

---

## Banco de dados em produção (resumo)

- É o arquivo **`database/database.sqlite`** na VM. Persistente (sobrevive a reboot e
  deploy). Suficiente para este app (baixo tráfego, 1 servidor).
- Migrations: `php artisan migrate --force` (o `deploy.sh` já roda).
- **Backup**: basta copiar o arquivo. Ex. diário via cron:
  ```bash
  0 3 * * * cp /var/www/valentine-day/database/database.sqlite /var/backups/wrapped-$(date +\%F).sqlite
  ```
- Fotos: backup da pasta `storage/app/public` junto.

## Problemas comuns

- **`SQLSTATE… attempt to write a readonly database`** → permissões do passo 5 (a pasta
  `database/` também precisa ser gravável, não só o arquivo).
- **Site não abre / timeout** → portas 80/443 (passo 1, os **dois** lugares).
- **HTTPS não emite** → o domínio DuckDNS precisa apontar para o IP e as portas abertas
  antes do `reload caddy`.
- **403/erro de assets** → rodou `npm run build`? `php artisan storage:link`?
- **Música não toca** → vídeo do YouTube com embed bloqueado; use outro link.
