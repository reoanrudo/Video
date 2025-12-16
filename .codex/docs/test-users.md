# テストユーザーのログイン情報

## 目的
ログイン動作確認のため、開発環境（`APP_ENV=local`）で `php artisan db:seed` を走らせるだけでテスト用のユーザーを複数作成できます。

## 手順
```bash
php artisan migrate
php artisan db:seed
```

APP_ENV が `local` でない場合、`DatabaseSeeder` は `TestUserSeeder` を呼び出さないため、本番で誤って作成されることはありません。

## 共通のログイン情報
- パスワード: `Password!12345`（Hash::make で保存）

| Email | User |
| --- | --- |
| `test@example.com` | Test User Alpha |
| `test+bravo@example.com` | Test User Bravo |
| `test+charlie@example.com` | Test User Charlie |
| `test+delta@example.com` | Test User Delta |
| `test+echo@example.com` | Test User Echo |

どのユーザーでも標準の認証フロー（`/login` が Livewire Volt で提供されていれば）でログイン可能です。
