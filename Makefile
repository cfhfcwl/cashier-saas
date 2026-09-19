up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f php nginx

migrate:
	docker compose exec php php think migrate:run

seed:
	docker compose exec php php think seed:run

shell:
	docker compose exec php sh

# 改了 Dockerfile 或 composer.json 之后用这个彻底重建
rebuild:
	docker compose build --no-cache
