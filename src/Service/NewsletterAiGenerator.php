<?php

namespace App\Service;

use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\TextResult;

class NewsletterAiGenerator
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $model = 'gpt-4o-mini',
    ) {
    }

    public function generateContent(string $topic, string $tone = '전문적인', ?string $additionalInstructions = null): string
    {
        $systemPrompt = <<<PROMPT
당신은 뉴스레터 콘텐츠 작성 전문가입니다.
이메일 뉴스레터를 위한 매력적이고 잘 구성된 HTML 콘텐츠를 작성합니다.
콘텐츠는 다음 형식을 따라야 합니다:
- 간결하고 명확한 소개
- 주요 내용 (소제목 포함)
- 마무리 문구
HTML 태그를 사용하여 형식을 지정하세요 (h2, p, ul, li, strong 등).
항상 한국어로 작성하세요.
PROMPT;

        $userPrompt = sprintf(
            '주제: %s%s%s 어조로 뉴스레터 콘텐츠를 작성해주세요.',
            $topic,
            $additionalInstructions ? "\n추가 지침: {$additionalInstructions}" : '',
            "\n\n{$tone}"
        );

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($userPrompt),
        );

        $result = $this->platform->invoke($this->model, $messages)->getResult();

        if ($result instanceof TextResult) {
            return $result->getContent();
        }

        throw new \RuntimeException('AI 콘텐츠 생성에 실패했습니다. TextResult를 받지 못했습니다.');
    }

    public function generateSubject(string $topic, string $tone = '전문적인'): string
    {
        $messages = new MessageBag(
            Message::forSystem('당신은 이메일 마케팅 전문가입니다. 높은 오픈율을 위한 매력적인 이메일 제목을 작성합니다. 한국어로 작성하고 30자 이내로 작성하세요.'),
            Message::ofUser(sprintf('주제: %s, 어조: %s - 이메일 제목을 작성해주세요.', $topic, $tone)),
        );

        $result = $this->platform->invoke($this->model, $messages)->getResult();

        if ($result instanceof TextResult) {
            return trim($result->getContent());
        }

        throw new \RuntimeException('AI 이메일 제목 생성에 실패했습니다.');
    }
}
