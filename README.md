# symfony-ai-practice

뉴스레터 이메일 전송 플랫폼 MVP - Symfony AI 패키지 활용

## 개요

이 프로젝트는 PHP Symfony 프레임워크와 **Symfony AI Bundle**을 활용하여 구현된 뉴스레터 이메일 발송 플랫폼 MVP입니다.

### 주요 기능

- **AI 콘텐츠 생성**: OpenAI GPT를 활용하여 뉴스레터 내용과 이메일 제목 자동 생성
- **뉴스레터 관리**: 작성, 수정, 삭제, 미리보기
- **구독자 관리**: 구독자 추가, 수정, 삭제, 활성화/비활성화
- **이메일 발송**: Symfony Mailer를 통해 활성 구독자 전체 발송

## 기술 스택

- **PHP 8.3** + **Symfony 7.4**
- **symfony/ai-bundle** + **symfony/ai-open-ai-platform** (OpenAI GPT-4o-mini)
- **Doctrine ORM** + **SQLite** (기본값, PostgreSQL 등으로 변경 가능)
- **Symfony Mailer**
- **Twig** + **Bootstrap 5**

## 설치 및 실행

### 1. 의존성 설치

```bash
composer install --ignore-platform-req=ext-redis
```

### 2. 환경 설정

`.env.local` 파일을 생성하고 설정합니다:

```env
# OpenAI API 키 설정
OPENAI_API_KEY=your_openai_api_key_here

# 이메일 발송 설정 (예: Gmail SMTP)
MAILER_DSN=smtp://user:password@smtp.gmail.com:587

# 발신자 정보
FROM_EMAIL=newsletter@yourdomain.com
FROM_NAME="Your Newsletter"

# 데이터베이스 (기본값: SQLite)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

### 3. 데이터베이스 설정

```bash
# SQLite 파일 생성
touch var/data.db

# 마이그레이션 실행
php bin/console doctrine:migrations:migrate
```

### 4. 서버 실행

```bash
php -S 0.0.0.0:8080 -t public/
```

브라우저에서 `http://localhost:8080` 접속

## 화면 구성

| 화면 | URL | 설명 |
|------|-----|------|
| 대시보드 | `/` | 통계 및 최근 뉴스레터 |
| 뉴스레터 목록 | `/newsletter` | 전체 뉴스레터 조회 |
| 새 뉴스레터 작성 | `/newsletter/new` | AI 생성 기능 포함 |
| 구독자 관리 | `/subscriber` | 구독자 CRUD |

## AI 기능 사용법

뉴스레터 작성 페이지에서:
1. **주제** 입력 (예: "2024년 AI 기술 트렌드")
2. **어조** 선택 (전문적인, 친근한, 공식적인 등)
3. **이메일 제목 생성** 또는 **콘텐츠 생성** 버튼 클릭
4. AI가 생성한 내용을 검토 후 저장
