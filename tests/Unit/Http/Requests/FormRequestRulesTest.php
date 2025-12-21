<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Analytics\ExportAnalyticsRequest;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Links\LinkRedirectRequest;
use App\Http\Requests\Lists\AddListMemberRequest;
use App\Http\Requests\Lists\StoreListRequest;
use App\Http\Requests\Lists\UpdateListRequest;
use App\Http\Requests\Messages\AddConversationMemberRequest;
use App\Http\Requests\Messages\CreateConversationRequest;
use App\Http\Requests\Messages\StoreMessageRequest;
use App\Http\Requests\Messages\UpdateConversationTitleRequest;
use App\Http\Requests\Moments\AddMomentItemRequest;
use App\Http\Requests\Moments\StoreMomentRequest;
use App\Http\Requests\Moments\UpdateMomentRequest;
use App\Http\Requests\Posts\QuoteRepostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Profile\DeleteMutedTermRequest;
use App\Http\Requests\Profile\DeleteUserRequest;
use App\Http\Requests\Profile\StoreMutedTermRequest;
use App\Http\Requests\Profile\UnblockUserRequest;
use App\Http\Requests\Profile\UnmuteUserRequest;
use App\Http\Requests\Profile\UpdateAnalyticsSettingsRequest;
use App\Http\Requests\Profile\UpdateDirectMessageSettingsRequest;
use App\Http\Requests\Profile\UpdateInterestHashtagsRequest;
use App\Http\Requests\Profile\UpdateNotificationPreferencesRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdatePinnedPostRequest;
use App\Http\Requests\Profile\UpdateProfileInformationRequest;
use App\Http\Requests\Profile\UpdateTimelineSettingsRequest;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Requests\Spaces\DecideSpeakerRequestRequest;
use App\Http\Requests\Spaces\PinSpacePostRequest;
use App\Http\Requests\Spaces\SetSpaceParticipantRoleRequest;
use App\Http\Requests\Spaces\StoreSpaceRequest;
use App\Models\Report;
use App\Models\User;
use App\Models\UserList;
use Closure;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class FormRequestRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_requests_authorize_and_rules_are_defined(): void
    {
        $user = User::factory()->create();

        foreach ($this->requestCases($user) as $case) {
            [$class, $data, $resolverUser] = $case;

            $request = $this->makeRequest($class, $data, $resolverUser);

            $this->assertIsArray($request->rules(), $class.' rules');
            $this->assertIsBool($request->authorize(), $class.' authorize');
        }
    }

    public function test_store_list_request_name_rule_skips_when_guest(): void
    {
        $closure = $this->findListNameClosure();

        Auth::shouldReceive('check')->andReturn(false);

        $failed = false;
        $closure('name', 'List name', function () use (&$failed): void {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_store_list_request_name_rule_enforces_limit(): void
    {
        $closure = $this->findListNameClosure();

        $user = Mockery::mock(User::class);
        $listsRelation = Mockery::mock(HasMany::class);
        $listsRelation->shouldReceive('count')->andReturn(UserList::MAX_LISTS_PER_OWNER);
        $user->shouldReceive('listsOwned')->andReturn($listsRelation);

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $failed = null;
        $closure('name', 'List name', function (string $message) use (&$failed): void {
            $failed = $message;
        });

        $this->assertIsString($failed);
    }

    /**
     * @return array<int, array{class-string<\Illuminate\Foundation\Http\FormRequest>, array<string, mixed>, ?User}>
     */
    private function requestCases(User $user): array
    {
        return [
            [AddListMemberRequest::class, [], null],
            [StoreListRequest::class, [], null],
            [UpdateListRequest::class, [], null],
            [AddConversationMemberRequest::class, [], null],
            [CreateConversationRequest::class, [], null],
            [StoreMessageRequest::class, [], null],
            [UpdateConversationTitleRequest::class, [], null],
            [AddMomentItemRequest::class, [], null],
            [StoreMomentRequest::class, [], null],
            [UpdateMomentRequest::class, [], null],
            [QuoteRepostRequest::class, [], null],
            [StorePostRequest::class, [], null],
            [ConfirmPasswordRequest::class, [], null],
            [ForgotPasswordRequest::class, [], null],
            [RegisterRequest::class, [], null],
            [ResetPasswordRequest::class, [], null],
            [DeleteMutedTermRequest::class, [], null],
            [DeleteUserRequest::class, [], null],
            [StoreMutedTermRequest::class, [], null],
            [UnblockUserRequest::class, [], null],
            [UnmuteUserRequest::class, [], null],
            [UpdateAnalyticsSettingsRequest::class, [], null],
            [UpdateDirectMessageSettingsRequest::class, [], null],
            [UpdateInterestHashtagsRequest::class, [], null],
            [UpdateNotificationPreferencesRequest::class, [], null],
            [UpdatePasswordRequest::class, [], null],
            [UpdatePinnedPostRequest::class, [], null],
            [UpdateProfileInformationRequest::class, [], $user],
            [UpdateTimelineSettingsRequest::class, [], null],
            [ExportAnalyticsRequest::class, [], null],
            [LinkRedirectRequest::class, [], null],
            [StoreReportRequest::class, ['reason' => Report::REASON_VIOLENCE], null],
            [SearchRequest::class, [], null],
            [DecideSpeakerRequestRequest::class, [], null],
            [PinSpacePostRequest::class, [], null],
            [SetSpaceParticipantRoleRequest::class, [], null],
            [StoreSpaceRequest::class, [], null],
        ];
    }

    /**
     * @param class-string<\Illuminate\Foundation\Http\FormRequest> $class
     */
    private function makeRequest(string $class, array $data, ?User $user): Request
    {
        /** @var \Illuminate\Foundation\Http\FormRequest $request */
        $request = $class::create('/', 'POST', $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(\Illuminate\Routing\Redirector::class));
        $request->setUserResolver(fn (): ?User => $user);

        return $request;
    }

    private function findListNameClosure(): Closure
    {
        $rules = StoreListRequest::rulesFor();
        foreach ($rules['name'] as $rule) {
            if ($rule instanceof Closure) {
                return $rule;
            }
        }

        $this->fail('StoreListRequest name rule closure not found.');
    }
}
