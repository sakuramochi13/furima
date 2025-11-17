<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Profile;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class HelloTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /** @test */
    public function 名前が未入力の場合はバリデーションエラーになる()
    {
        $response = $this
            ->from('/register')
            ->post('/register', $this->validData([
                'name' => '', // ← 名前だけ未入力
            ]));


        $response->assertRedirect('/register');

        // name にエラーがある
        $response->assertSessionHasErrors(['name']);

        // エラーメッセージが表示される
        $this->get('/register')
            ->assertSee('お名前を入力してください');
    }

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションエラーになる()
    {
        $response = $this
            ->from('/register')
            ->post('/register', $this->validData([
                'email' => '', // ← メールだけ未入力
            ]));

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors(['email']);

        $this->get('/register')
            ->assertSee('メールアドレスを入力してください');
    }

    /** @test */
    public function パスワードが未入力の場合はバリデーションエラーになる()
    {
        $response = $this
            ->from('/register')
            ->post('/register', $this->validData([
                'password'              => '',
                'password_confirmation' => '',
            ]));

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors(['password']);

        $this->get('/register')
            ->assertSee('パスワードを入力してください');
    }

    /** @test */
    public function パスワードが7文字以下の場合はバリデーションエラーになる()
    {
        $shortPassword = 'short7'; // 7文字想定

        $response = $this
            ->from('/register')
            ->post('/register', $this->validData([
                'password'              => $shortPassword,
                'password_confirmation' => $shortPassword,
            ]));

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors(['password']);

        $this->get('/register')
            ->assertSee('パスワードは8文字以上で入力してください');
    }

    /** @test */
    public function パスワードが確認用と一致しない場合はバリデーションエラーになる()
    {
        $response = $this
            ->from('/register')
            ->post('/register', $this->validData([
                'password'              => 'password123',
                'password_confirmation' => 'different123',
            ]));

        $response->assertRedirect('/register');

        $response->assertSessionHasErrors(['password']);

        $this->get('/register')
            ->assertSee('パスワードと一致しません');
    }

    /** @test */
    public function 全ての項目が正しく入力されていれば会員登録されプロフィール設定画面に遷移する()
    {
        $response = $this->post('/register', $this->validData());

        // users テーブルにレコードが作成されているか
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // ログイン状態になっていること（Fortifyのデフォルト挙動）
        $this->assertAuthenticated();

        // プロフィール設定画面にリダイレクトされる想定
        // （ProfileController@edit のルート名: profile.edit）
        $response->assertRedirect('/email/verify');
    }


    /**
     * ログインに有効なデータ（標準データ）
     */
    private function validLoginData(array $overrides = [])
    {
        return array_merge([
            'email'    => 'test@example.com',
            'password' => 'password123',
        ], $overrides);
    }

    /** @test */
    public function ログイン時にメールアドレスが未入力だとバリデーションエラーになる()
    {
        $response = $this
            ->from('/login')
            ->post('/login', $this->validLoginData([
                'email' => '',
            ]));

        $response->assertRedirect('/login');

        // フォームリクエスト LoginRequest.php のメッセージに一致させる
        $response->assertSessionHasErrors(['email']);

        $this->get('/login')
            ->assertSee('メールアドレスを入力してください');
    }

    /** @test */
    public function ログイン時にパスワードが未入力だとバリデーションエラーになる()
    {
        $response = $this
            ->from('/login')
            ->post('/login', $this->validLoginData([
                'password' => '',
            ]));

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors(['password']);

        $this->get('/login')
            ->assertSee('パスワードを入力してください');
    }

    /** @test */
    public function ログイン時に入力情報が間違っているとエラーメッセージが表示される()
    {
        // DB にユーザーはいるが、パスワード違いでログイン失敗 → でも今回は
        // あえて「存在しない情報」でログインさせるテスト内容なのでユーザー作成しない

        $response = $this
            ->from('/login')
            ->post('/login', [
                'email'    => 'notfound@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertRedirect('/login');

        // カスタムメッセージ（LoginRequest または LoginController）に合わせる
        $response->assertSessionHasErrors(['email']);

        $this->get('/login')
            ->assertSee('ログイン情報が登録されていません');
    }

    /** @test */
    public function 正しい情報が入力された場合はログイン処理が成功する()
    {
        // 正しいユーザーを作成
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this
            ->from('/login')
            ->post('/login', $this->validLoginData());

        // ログイン済みになっているか
        $this->assertAuthenticatedAs($user);

        // ログイン後の遷移先（Fortifyデフォルトは / だが、設定次第）
        // 今のあなたの挙動に合わせる必要がある場合はここを変更してね
        $response->assertRedirect('/');
    }

    /** @test */
    public function ログアウトができる()
    {
        // 1. ログインユーザーを作成してログイン状態にする
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user);

        // 念のため、ログイン状態であることを確認
        $this->assertAuthenticated();

        // 2. ログアウトリクエストを送信（POST /logout）
        $response = $this->post('/logout');

        // ログアウト後は認証されていないこと
        $this->assertGuest();

        // 3. 遷移先は `/`（あなたの仕様に合わせる）
        $response->assertRedirect('/');
    }

/** @test */
    public function 商品一覧ページで全商品が表示される()
    {
        // 商品を3件作成（出品者は誰でもOK・未ログイン想定）
        $products = Product::factory()->count(3)->create();

        // 商品一覧ページにアクセス（未ログイン）
        $response = $this->get('/');

        $response->assertStatus(200);

        // 作成した商品名がすべてHTML内に含まれているか
        foreach ($products as $product) {
            $response->assertSee($product->product_name);
        }
    }

    /** @test */
    public function 商品一覧ページで購入済み商品には_Sold_ラベルが表示される()
    {
        // 未購入の商品
        $unsold = Product::factory()->create([
            'status' => 'listed', // あなたのproductsテーブルのステータスに合わせて
            'product_name' => '未購入の商品',
        ]);

        // 購入済み（売り切れ）の商品
        $sold = Product::factory()->create([
            'status' => 'sold',   // ← 「購入済み」を表す状態に合わせる
            'product_name' => '購入済みの商品',
        ]);

        // 一覧ページにアクセス
        $response = $this->get('/');

        $response->assertStatus(200);

        // 売り切れ商品が一覧に出ていること
        $response->assertSee($sold->product_name);

        // 売り切れ商品用のラベルが表示されていること
        // Blade 側で「Sold」と表示している想定
        $response->assertSee('SOLD');
    }

    /** @test */
    public function ログイン時は自分が出品した商品は商品一覧に表示されない()
    {
        // ログインユーザー
        $me = User::factory()->create();

        // 自分が出品した商品
        $myProduct = Product::factory()->create([
            'user_id' => $me->id,
            'product_name' => '自分の商品',
        ]);

        // 他人が出品した商品
        $otherUser = User::factory()->create();
        $othersProduct = Product::factory()->create([
            'user_id' => $otherUser->id,
            'product_name' => '他人の商品',
        ]);

        // ログイン状態にする
        $this->actingAs($me);

        // 商品一覧ページにアクセス
        $response = $this->get('/');

        $response->assertStatus(200);

        // 自分の商品は表示されない（notOwnedBy(scope)のテスト）
        $response->assertDontSee('自分の商品');

        // 他人の商品は表示される
        $response->assertSee('他人の商品');
    }

    /** @test */
    public function いいねアイコンを押下するといいねが登録され_詳細ページで件数とアイコンが更新される()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'product_name' => 'いいねテスト商品',
        ]);

        $this->actingAs($user);

        // 事前はいいね無し
        $this->assertDatabaseMissing('likes', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        // 1. いいねを押す（POST）
        $response = $this
            ->from("/item/{$product->id}")
            ->post("/item/{$product->id}/like");

        // 2. 詳細ページに戻る
        $response->assertRedirect("/item/{$product->id}");

        // 3. DB上でいいねが1件登録されている
        $this->assertDatabaseHas('likes', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals(
            1,
            Like::where('product_id', $product->id)->count()
        );

        // 4. 詳細ページを開き直して、アイコンとカウンタを確認
        $show = $this->get("/item/{$product->id}");

        $show->assertStatus(200);

        // いいね済みなので「濃い星」のアイコン（icon-star-2.svg）が出る
        $show->assertSee('icon-star-2.svg');

        // いいね数 1 が表示されている
        $show->assertSee('1');
    }
    /** @test */
    public function いいね済みの商品詳細ページでは濃い星アイコンが表示される()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'product_name' => 'いいね済み商品',
        ]);

        // 事前にいいね状態を作る
        Like::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user);

        $response = $this->get("/item/{$product->id}");

        $response->assertStatus(200);

        // いいね済みなので「濃い星」アイコンが表示される
        $response->assertSee('icon-star-2.svg');

        // 逆に「未いいね」用アイコンは出てこないはず
        $response->assertDontSee('icon-star-1.svg');
    }

    /** @test */
    public function いいね済みの商品で再度アイコンを押下するといいねが解除され_件数とアイコンが戻る()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'product_name' => 'いいね解除テスト商品',
        ]);

        // 事前に1件いいねしておく
        Like::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user);

        $this->assertEquals(
            1,
            Like::where('product_id', $product->id)->count()
        );

        // 1. 再度いいねボタン押下 → 解除
        $response = $this
            ->from("/item/{$product->id}")
            ->post("/item/{$product->id}/like");

        $response->assertRedirect("/item/{$product->id}");

        // セッションにステータスが入っている実装ならこれも確認
        $response->assertSessionHas('like_status', 'unliked');

        // 2. DB上でいいねが削除されている
        $this->assertDatabaseMissing('likes', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals(
            0,
            Like::where('product_id', $product->id)->count()
        );

        // 3. 詳細ページを開き直して、アイコンと件数を確認
        $show = $this->get("/item/{$product->id}");

        $show->assertStatus(200);

        // 未いいねなので「薄い星」アイコン（icon-star-1.svg）が出る
        $show->assertSee('icon-star-1.svg');
        $show->assertDontSee('icon-star-2.svg');

        // いいね数 0 が表示されている（文字列 "0" がHTMLに含まれている想定）
        $show->assertSee('0');
    }

        /** @test */
    public function ログイン済みユーザーはコメントを送信できる()
    {
        // ログインユーザー（メール認証済みにしておくと安心）
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // コメント対象の商品
        $product = Product::factory()->create([
            'product_name' => 'コメントテスト商品',
        ]);

        $this->actingAs($user);

        $this->assertEquals(0, Comment::count());

        // コメント送信（詳細ページから送った想定で from を付ける）
        $commentBody = 'これはテストコメントです。';

        $response = $this
            ->from("/item/{$product->id}")
            ->post(route('comments.store', ['item' => $product->id]), [
                'body' => $commentBody,
            ]);

        // 元の詳細ページに戻る
        $response->assertRedirect("/item/{$product->id}");

        // DBにコメントが保存されている
        $this->assertDatabaseHas('comments', [
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'body'       => $commentBody,
        ]);

        // 件数が1件に増えている
        $this->assertEquals(1, Comment::where('product_id', $product->id)->count());

        // 詳細ページにコメント内容が表示されている
        $show = $this->get("/item/{$product->id}");
        $show->assertStatus(200);
        $show->assertSee($commentBody);

        // コメント数の表示（コメント(<span>1</span>)）を確認
        $show->assertSee('コメント(<span>1</span>)', false); // HTMLそのままチェック
    }

        /** @test */
    public function ログイン前のユーザーはコメントを送信できない()
    {
        $product = Product::factory()->create([
            'product_name' => 'ゲストコメントテスト商品',
        ]);

        $commentBody = 'ゲストのコメント';

        // ログインせずにコメント送信
        $response = $this->post(route('comments.store', ['item' => $product->id]), [
            'body' => $commentBody,
        ]);

        // authミドルウェアによりログインページへリダイレクトされるはず
        $response->assertRedirect(route('login'));

        // DBにはコメントが保存されていない
        $this->assertDatabaseMissing('comments', [
            'product_id' => $product->id,
            'body'       => $commentBody,
        ]);
    }

        /** @test */
    public function コメントが空の場合は_コメントを入力してください_のバリデーションエラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $product = Product::factory()->create();

        $this->actingAs($user);

        $response = $this
            ->from("/item/{$product->id}")
            ->post(route('comments.store', ['item' => $product->id]), [
                'body' => '', // ← 空
            ]);

        // 元のページに戻される
        $response->assertRedirect("/item/{$product->id}");

        // body にバリデーションエラーがある
        $response->assertSessionHasErrors(['body']);

        // ページにエラーメッセージが表示される
        $show = $this->get("/item/{$product->id}");
        $show->assertStatus(200);
        $show->assertSee('コメントを入力してください');
    }

        /** @test */
    public function コメントが255文字を超える場合は_255文字以内_のバリデーションエラーになる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $product = Product::factory()->create();

        $this->actingAs($user);

        // 256文字の文字列を作成
        $longComment = str_repeat('あ', 256);

        $response = $this
            ->from("/item/{$product->id}")
            ->post(route('comments.store', ['item' => $product->id]), [
                'body' => $longComment,
            ]);

        $response->assertRedirect("/item/{$product->id}");

        $response->assertSessionHasErrors(['body']);

        $show = $this->get("/item/{$product->id}");
        $show->assertStatus(200);
        $show->assertSee('コメントは255文字以内で入力してください');
    }

    /** @test */
    public function プロフィール編集画面でユーザー情報の初期値が表示される()
    {
    // 1. ログインユーザー & プロフィールを用意
        $user = User::factory()->create([
            'name'              => 'テストユーザー',
            'email_verified_at' => now(),
        ]);

        $profile = Profile::factory()->create([
            'user_id'           => $user->id,
            'profile_image_url' => '/storage/profiles/test-profile.png',
            'postal_code'       => '123-4567',
            'address'           => 'テスト県テスト市1-2-3',
            'building'          => 'テストビル202',
        ]);

    // 2. ログイン状態でプロフィール編集画面を開く
        $this->actingAs($user);

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);

    // 3. プロフィール画像
        $response->assertSee('test-profile.png');

    // 4. ユーザー名
        $response->assertSee('テストユーザー');

    // 5. 郵便番号
        $response->assertSee('123-4567');

    // 6. 住所
        $response->assertSee('テスト県テスト市1-2-3');

    // 7. 建物名（今回追加部分）
        $response->assertSee('テストビル202');
    }

    /** @test */
    public function 出品画面で必要な情報が保存される()
    {
    // 1. ログインユーザーを用意
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

    // 2. カテゴリをいくつか用意
        $category1 = Category::factory()->create(['name' => 'カテゴリA']);
        $category2 = Category::factory()->create(['name' => 'カテゴリB']);

    // 3. ストレージをフェイク（実際のstorageにファイルを作らないようにする）
        Storage::fake('public');

    // 実際の画像生成はせず、ダミーファイルだけ作成（GD不要）
        $imageFile = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');

        $postData = [
            'image'         => $imageFile,
            'category_ids'  => [$category1->id, $category2->id],
            'condition'     => 'excellent',                // conditionOptions の key に合わせる
            'product_name'  => 'テスト出品商品',
            'brand_name'    => 'テストブランド',
            'description'   => 'これはテスト用の商品説明です。',
            'price'         => 12345,
        ];

    // 5. 出品処理にPOST
        $response = $this->post(route('sell.store'), $postData);

    // 6. 一覧ページにリダイレクトされる
        $response->assertRedirect(route('items.index'));
        $response->assertSessionHas('success', '出品が完了しました。');

    // 7. productsテーブルに商品が保存されている
        $this->assertDatabaseHas('products', [
            'user_id'      => $user->id,
            'product_name' => 'テスト出品商品',
            'description'  => 'これはテスト用の商品説明です。',
            'price'        => 12345,
            'condition'    => 'excellent',
            'status'       => 'listed',
        ]);

    // 保存された商品を取得
        $product = Product::where('product_name', 'テスト出品商品')->first();
        $this->assertNotNull($product, '商品が保存されていません');

    // 8. 画像ファイルがstorageに保存されている
    // controllerでは $path = store('products', 'public');
    // image_urlには Storage::url($path) なので '/storage/products/xxxx.jpg' 形式になる
        $this->assertStringStartsWith('/storage/products/', $product->image_url);

    // 実際のパス部分を取り出して存在チェック（先頭の '/storage/' を削って確認）
        $storedPath = ltrim(str_replace('/storage/', '', $product->image_url), '/');
        Storage::disk('public')->assertExists($storedPath);

    // 9. brandsテーブルにブランドが作成されている
        $this->assertDatabaseHas('brands', [
            'name' => 'テストブランド',
        ]);

        $brand = Brand::where('name', 'テストブランド')->first();
        $this->assertNotNull($brand);
        $this->assertEquals($brand->id, $product->brand_id);

    // 10. 中間テーブル category_product にカテゴリの紐付けが保存されている
        $this->assertDatabaseHas('category_product', [
            'product_id'  => $product->id,
            'category_id' => $category1->id,
        ]);

        $this->assertDatabaseHas('category_product', [
            'product_id'  => $product->id,
            'category_id' => $category2->id,
        ]);
    }

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        Notification::fake();

        $postData = [
            'name'                  => 'メール認証テストユーザー',
            'email'                 => 'verifytest@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

    // 会員登録
        $response = $this->post(route('register.store'), $postData);

    // verification.notice へリダイレクトされる（/email/verify）
        $response->assertRedirect(route('verification.notice'));

    // ユーザーが作成されている
        $user = User::where('email', 'verifytest@example.com')->first();
        $this->assertNotNull($user);

    // VerifyEmail 通知（認証メール）が送信されていることを確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    /** @test */
    public function 認証案内画面にメール認証サイトへのリンクが表示される()
    {
    // 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

    // /email/verify（verification.notice）を開く
        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);

    // メッセージ文言
        $response->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');
        $response->assertSee('メール認証を完了してください。');

    // 「認証はこちらから」ボタンのテキスト
        $response->assertSee('認証はこちらから');

    // MailHogへのリンク href="http://localhost:8025" が含まれている（aタグとして出ている）
        $response->assertSee('http://localhost:8025', false);
    }

    /** @test */
    public function メール認証を完了するとプロフィール設定画面に遷移する()
    {
    // 未認証ユーザーを用意
        $user = User::factory()->create([
            'email'             => 'verifyuser@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

    // Laravel 標準の VerifyEmail が作る hash と同じ値
        $hash = sha1($user->email);

    // verification.verify 用の署名付きURLを生成
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash]
        );

    // そのURLにアクセス（＝メール内のリンクをクリックした動作に相当）
        $response = $this->get($signedUrl);

    // プロフィール設定画面にリダイレクトされる
        $response->assertRedirect(route('profile.edit'));

    // email_verified_at がセットされていること
        $this->assertNotNull($user->fresh()->email_verified_at);
    }


}
