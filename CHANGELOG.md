# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [1.6.0](https://github.com/melasistema/clients-melasistema/compare/v1.5.0...v1.6.0) (2026-08-02)


### Features

* **report:** time report of tracked hours and billable value by day and project ([ed59f4e](https://github.com/melasistema/clients-melasistema/commit/ed59f4e679f927d952dcb2ab4bef0f05a2d1933d))
* **time-tracking:** dated time-entries ledger for banked work sessions ([e65fab8](https://github.com/melasistema/clients-melasistema/commit/e65fab83563b699043f26c549b061aea6e250df3))

## [1.5.0](https://github.com/melasistema/clients-melasistema/compare/v1.4.0...v1.5.0) (2026-08-01)


### Features

* **security:** headers, CSP, HTTPS hardening & dependency cleanup ([fc226f6](https://github.com/melasistema/clients-melasistema/commit/fc226f6544797fe8fee49b903980b4d2e6ac378c))

## [1.4.0](https://github.com/melasistema/clients-melasistema/compare/v1.3.4...v1.4.0) (2026-08-01)


### Features

* **security:** add headers & env hardening, patch and de-cruft deps ([43690b6](https://github.com/melasistema/clients-melasistema/commit/43690b6544f085c28a4936034917bad9c476c0dc))

### [1.3.4](https://github.com/melasistema/clients-melasistema/compare/v1.3.3...v1.3.4) (2026-08-01)

### [1.3.3](https://github.com/melasistema/clients-melasistema/compare/v1.3.2...v1.3.3) (2026-08-01)

### [1.3.2](https://github.com/melasistema/clients-melasistema/compare/v1.3.1...v1.3.2) (2026-08-01)

### [1.3.1](https://github.com/melasistema/clients-melasistema/compare/v1.3.0...v1.3.1) (2026-08-01)

## [1.3.0](https://github.com/melasistema/clients-melasistema/compare/v1.2.0...v1.3.0) (2026-07-31)


### Features

* **tasks:** live total readout under the time picker ([8b696b3](https://github.com/melasistema/clients-melasistema/commit/8b696b3641758c015937ab49430b596ce3a63a73))

## [1.2.0](https://github.com/melasistema/clients-melasistema/compare/v1.1.0...v1.2.0) (2026-07-31)


### Features

* **dashboard:** owner overview with earnings, payments & live timer ([fa7b2f0](https://github.com/melasistema/clients-melasistema/commit/fa7b2f040525b82da2b004a7b0b27f3074f91fde))
* **i18n:** config-driven UI locale with lang files + __() composable ([0c0f089](https://github.com/melasistema/clients-melasistema/commit/0c0f089fcc9339b93c620a0b2df3b4eb8ace92dc))
* **money:** centralize currency/locale into config + shared formatter ([636ce27](https://github.com/melasistema/clients-melasistema/commit/636ce2746c614a953d4899a7b94a5e755cd7c86d))
* **tasks:** segmented H/M/S time picker on task edit ([98735ed](https://github.com/melasistema/clients-melasistema/commit/98735ed0933b23ddf9e3930ec0b6905fbd9e7287))
* **timer:** single running timer + persistent resumable bar ([49ef257](https://github.com/melasistema/clients-melasistema/commit/49ef2576f1459a489d870b22aa614012bcf281af))

## [1.1.0](https://github.com/melasistema/clients-melasistema/compare/v1.0.0...v1.1.0) (2026-07-26)


### Features

* add completion nudge ([83f83ff](https://github.com/melasistema/clients-melasistema/commit/83f83ff3b9d4ae4e369ee9eaf8daaa306ee2b67f))
* **projects:** completion & payment UI, non-billable surfaces ([fc6d5cf](https://github.com/melasistema/clients-melasistema/commit/fc6d5cf44c8c042ec8cd0db38b4aee13c02a50ae))
* **projects:** completion, staged-payment ledger & non-billable projects (backend) ([ec61b24](https://github.com/melasistema/clients-melasistema/commit/ec61b241a0667a64d7eb38f0cae9c6617e0ff9d4))

## [1.0.0](https://github.com/melasistema/clients-melasistema/compare/v0.0.63...v1.0.0) (2026-07-26)

### [0.0.63](https://github.com/melasistema/clients-melasistema/compare/v0.0.62...v0.0.63) (2026-07-26)


### Features

* **auth:** single-user by default with CLI provisioning and opt-in registration ([04e9454](https://github.com/melasistema/clients-melasistema/commit/04e94549a2bf817dd1b6537d4ce459a2b72d0992))

### [0.0.62](https://github.com/melasistema/clients-melasistema/compare/v0.0.61...v0.0.62) (2026-07-26)


### Bug Fixes

* fix deployment pipeline ([3904250](https://github.com/melasistema/clients-melasistema/commit/3904250aebfcb3b9794d57c5e66b88eb279651d6))

### [0.0.61](https://github.com/melasistema/clients-melasistema/compare/v0.0.60...v0.0.61) (2026-07-26)

### [0.0.60](https://github.com/melasistema/clients-melasistema/compare/v0.0.59...v0.0.60) (2026-07-26)


### Features

* **data:** soft-delete the domain hierarchy and scope client email per user ([3c1bdcd](https://github.com/melasistema/clients-melasistema/commit/3c1bdcde8720b02fcb551ae595b4c8825db93d08))
* **security:** enforce per-user ownership across clients, projects, tasks ([8493aea](https://github.com/melasistema/clients-melasistema/commit/8493aeab932e83da1999b51c0fba819c44b81c62))
* **trash:** add trash page with restore and permanent delete ([2ea6802](https://github.com/melasistema/clients-melasistema/commit/2ea6802e31c9d6bef3e024217a4994a05104db5d))


### Bug Fixes

* **earnings:** compute money in integer cents to avoid float drift ([6d7b908](https://github.com/melasistema/clients-melasistema/commit/6d7b908117da19adfeb269da790161b4b1615d2b))

### [0.0.59](https://github.com/melasistema/clients-melasistema/compare/v0.0.58...v0.0.59) (2026-07-25)

### [0.0.58](https://github.com/melasistema/clients-melasistema/compare/v0.0.57...v0.0.58) (2026-07-25)

### [0.0.57](https://github.com/melasistema/clients-melasistema/compare/v0.0.56...v0.0.57) (2026-07-25)


### Features

* **earnings:** fix rollup, add demo seeds and repair the Pest suite ([29b8088](https://github.com/melasistema/clients-melasistema/commit/29b8088f0fa48b9170b8e6cff69130bdf7bc050f))

### [0.0.56](https://github.com/melasistema/clients-melasistema/compare/v0.0.55...v0.0.56) (2025-08-19)


### Features

* **earnings:** Calculate and display earnings for tasks, projects, and client ([39704e1](https://github.com/melasistema/clients-melasistema/commit/39704e11867d1c767517ae27504a040421a9f0a8))

### [0.0.55](https://github.com/melasistema/clients-melasistema/compare/v0.0.54...v0.0.55) (2025-08-08)


### Features

* **clients:** Enable client deletion ([8efcc4a](https://github.com/melasistema/clients-melasistema/commit/8efcc4a392250659a21863e76c6a9cb993f98fea))

### [0.0.54](https://github.com/melasistema/clients-melasistema/compare/v0.0.53...v0.0.54) (2025-08-07)


### Features

* **auth:** Disable user registration ([47e7cb2](https://github.com/melasistema/clients-melasistema/commit/47e7cb234d7af5496ec7dc42492b3cbf17a09779))

### [0.0.53](https://github.com/melasistema/clients-melasistema/compare/v0.0.52...v0.0.53) (2025-08-03)

### [0.0.52](https://github.com/melasistema/clients-melasistema/compare/v0.0.51...v0.0.52) (2025-08-03)

### [0.0.51](https://github.com/melasistema/clients-melasistema/compare/v0.0.50...v0.0.51) (2025-08-03)

### [0.0.50](https://github.com/melasistema/clients-melasistema/compare/v0.0.49...v0.0.50) (2025-08-03)


### Bug Fixes

* **deploy:** Remove standard-version ([fd3c138](https://github.com/melasistema/clients-melasistema/commit/fd3c13836fb2fcbce2ca9011145750fa20190766))

### [0.0.31](https://github.com/melasistema/clients-melasistema/compare/v0.0.30...v0.0.31) (2025-08-03)

### [0.0.49](https://github.com/melasistema/clients-melasistema/compare/v0.0.48...v0.0.49) (2025-08-03)

### [0.0.48](https://github.com/melasistema/clients-melasistema/compare/v0.0.47...v0.0.48) (2025-08-03)

### [0.0.47](https://github.com/melasistema/clients-melasistema/compare/v0.0.46...v0.0.47) (2025-08-03)

### [0.0.46](https://github.com/melasistema/clients-melasistema/compare/v0.0.45...v0.0.46) (2025-08-03)

### [0.0.45](https://github.com/melasistema/clients-melasistema/compare/v0.0.44...v0.0.45) (2025-08-03)

### [0.0.44](https://github.com/melasistema/clients-melasistema/compare/v0.0.43...v0.0.44) (2025-08-03)

### [0.0.43](https://github.com/melasistema/clients-melasistema/compare/v0.0.42...v0.0.43) (2025-08-03)

### [0.0.42](https://github.com/melasistema/clients-melasistema/compare/v0.0.41...v0.0.42) (2025-08-03)

### [0.0.41](https://github.com/melasistema/clients-melasistema/compare/v0.0.40...v0.0.41) (2025-08-03)

### [0.0.40](https://github.com/melasistema/clients-melasistema/compare/v0.0.39...v0.0.40) (2025-08-03)

### [0.0.39](https://github.com/melasistema/clients-melasistema/compare/v0.0.38...v0.0.39) (2025-08-03)

### [0.0.38](https://github.com/melasistema/clients-melasistema/compare/v0.0.37...v0.0.38) (2025-08-03)

### [0.0.37](https://github.com/melasistema/clients-melasistema/compare/v0.0.36...v0.0.37) (2025-08-03)

### [0.0.36](https://github.com/melasistema/clients-melasistema/compare/v0.0.35...v0.0.36) (2025-08-03)

### [0.0.35](https://github.com/melasistema/clients-melasistema/compare/v0.0.34...v0.0.35) (2025-08-03)

### [0.0.34](https://github.com/melasistema/clients-melasistema/compare/v0.0.33...v0.0.34) (2025-08-03)

### [0.0.33](https://github.com/melasistema/clients-melasistema/compare/v0.0.32...v0.0.33) (2025-08-03)

### [0.0.32](https://github.com/melasistema/clients-melasistema/compare/v0.0.31...v0.0.32) (2025-08-03)

### [0.0.31](https://github.com/melasistema/clients-melasistema/compare/v0.0.30...v0.0.31) (2025-08-03)


### Features

* **deploy:** Add PAT_TOKEN ([50c6bcd](https://github.com/melasistema/clients-melasistema/commit/50c6bcdc08d3806b1b16c68d2bc6d48b3fc6a159))

### [0.0.30](https://github.com/melasistema/clients-melasistema/compare/v0.0.29...v0.0.30) (2025-08-03)


### Bug Fixes

* **deploy:** Fix standard-version workflow order ([78e61f2](https://github.com/melasistema/clients-melasistema/commit/78e61f2c230440ff898ee217df92b975ecd3530d))

### [0.0.29](https://github.com/melasistema/clients-melasistema/compare/v0.0.28...v0.0.29) (2025-08-03)


### Features

* **deploy:** Add standard-version for automatic version bump ([e0f797f](https://github.com/melasistema/clients-melasistema/commit/e0f797fda1bb9ce7c302b0defde2ae0e23606910))

### [0.0.28](https://github.com/melasistema/clients-melasistema/compare/v0.0.27...v0.0.28) (2025-08-03)


### Bug Fixes

* **logo:** Remove default logo ([d6be636](https://github.com/melasistema/clients-melasistema/commit/d6be6368f0383b0e65cf0c63b70520b03c834225))

### [0.0.27](https://github.com/melasistema/clients-melasistema/compare/v0.0.26...v0.0.27) (2025-08-03)


### Features

* **components:** Add logo ([7471231](https://github.com/melasistema/clients-melasistema/commit/747123176cc0559513bd9c84c7219bcb7301bd15))
* **deploy:** add symbolic link to deploy workflow ([7b97727](https://github.com/melasistema/clients-melasistema/commit/7b9772730c55466fcead78d42be71f934361018a))

### [0.0.26](https://github.com/melasistema/clients-melasistema/compare/v0.0.25...v0.0.26) (2025-08-03)

### [0.0.25](https://github.com/melasistema/clients-melasistema/compare/v0.0.24...v0.0.25) (2025-08-03)


### Bug Fixes

* **deploy:** Fix folder name ([1ad1a4a](https://github.com/melasistema/clients-melasistema/commit/1ad1a4a750899a8e8ce86ff269a62a25ce2f1495))
* **deploy:** Fix folder name ([f2d6b01](https://github.com/melasistema/clients-melasistema/commit/f2d6b01e77931939320792a3f248c761cf9575bd))

### [0.0.24](https://github.com/melasistema/clients-melasistema/compare/v0.0.23...v0.0.24) (2025-08-03)

### [0.0.23](https://github.com/melasistema/clients-melasistema/compare/v0.0.22...v0.0.23) (2025-08-03)


### Bug Fixes

* **deploy:** Use wildcard for source path ([231cda8](https://github.com/melasistema/clients-melasistema/commit/231cda8b95658041aea3be75c6e416be5d689729))

### [0.0.22](https://github.com/melasistema/clients-melasistema/compare/v0.0.21...v0.0.22) (2025-08-03)

### [0.0.21](https://github.com/melasistema/clients-melasistema/compare/v0.0.20...v0.0.21) (2025-08-03)

### [0.0.20](https://github.com/melasistema/clients-melasistema/compare/v0.0.19...v0.0.20) (2025-08-03)

### [0.0.19](https://github.com/melasistema/clients-melasistema/compare/v0.0.18...v0.0.19) (2025-08-03)


### Bug Fixes

* **deploy:** Add environment name ([98a301b](https://github.com/melasistema/clients-melasistema/commit/98a301ba482f985e6d8765567d6ce9faf6585864))

### [0.0.18](https://github.com/melasistema/clients-melasistema/compare/v0.0.17...v0.0.18) (2025-08-03)


### Bug Fixes

* **deploy:** Remove tests ([6f96577](https://github.com/melasistema/clients-melasistema/commit/6f96577129d2b62e022e4bf4eaced95eca914374))

### [0.0.17](https://github.com/melasistema/clients-melasistema/compare/v0.0.16...v0.0.17) (2025-08-03)


### Bug Fixes

* **deploy:** Fix production branch ([77e11f9](https://github.com/melasistema/clients-melasistema/commit/77e11f9c44ae75075c8f651b8e45de2b8cf3cb98))

### [0.0.16](https://github.com/melasistema/clients-melasistema/compare/v0.0.15...v0.0.16) (2025-08-03)


### Bug Fixes

* **deploy:** Fix variables ([412e916](https://github.com/melasistema/clients-melasistema/commit/412e9163070d1c697cacf4d6704613d4844267ea))

### [0.0.15](https://github.com/melasistema/clients-melasistema/compare/v0.0.14...v0.0.15) (2025-08-03)


### Bug Fixes

* **deploy:** Tryout differently ([e5d0582](https://github.com/melasistema/clients-melasistema/commit/e5d0582df92b30972d5ac7c80c5071f3f37bb01f))

### [0.0.14](https://github.com/melasistema/clients-melasistema/compare/v0.0.13...v0.0.14) (2025-08-03)


### Bug Fixes

* **deploy:** Still trying ([febf027](https://github.com/melasistema/clients-melasistema/commit/febf027463450f85e5ddb85f5b7af9b5c923355b))

### [0.0.13](https://github.com/melasistema/clients-melasistema/compare/v0.0.12...v0.0.13) (2025-08-03)


### Bug Fixes

* **deploy:** Still try to fix SSH problems ([0f7a3cb](https://github.com/melasistema/clients-melasistema/commit/0f7a3cb9d923a520d496c68243a667d730bc7bce))

### [0.0.12](https://github.com/melasistema/clients-melasistema/compare/v0.0.11...v0.0.12) (2025-08-03)


### Bug Fixes

* **deploy:** add SSH to the agent ([f7405ad](https://github.com/melasistema/clients-melasistema/commit/f7405ad442437eb0c13018f335970012a64c5957))

### [0.0.11](https://github.com/melasistema/clients-melasistema/compare/v0.0.10...v0.0.11) (2025-08-03)


### Bug Fixes

* **deploy:** Try to let agents handle the ssh key ([114fa62](https://github.com/melasistema/clients-melasistema/commit/114fa62b3fb5560e1885c7ccb35bb65724bd8774))

### [0.0.10](https://github.com/melasistema/clients-melasistema/compare/v0.0.9...v0.0.10) (2025-08-03)


### Bug Fixes

* **deploy:** Still tryout/discovering/testing ([0bc9a2e](https://github.com/melasistema/clients-melasistema/commit/0bc9a2e19d16384a3f866e5445cb2d9188817f26))

### [0.0.9](https://github.com/melasistema/clients-melasistema/compare/v0.0.8...v0.0.9) (2025-08-03)


### Bug Fixes

* **deploy:** Still try to make deployment script works as intended ([737ef97](https://github.com/melasistema/clients-melasistema/commit/737ef970b546a7c9572fc1659f34eedc1c4fcf02))

### [0.0.8](https://github.com/melasistema/clients-melasistema/compare/v0.0.7...v0.0.8) (2025-08-03)


### Bug Fixes

* **deploy:** Use burnett01 v.1.5 ([7fbe669](https://github.com/melasistema/clients-melasistema/commit/7fbe66943045ab481d3fd5659bc2fc59b246ef1d))

### [0.0.7](https://github.com/melasistema/clients-melasistema/compare/v0.0.6...v0.0.7) (2025-08-03)


### Bug Fixes

* **deploy:** Add absolute server path ([635364c](https://github.com/melasistema/clients-melasistema/commit/635364c258eedba039c0216a2128cc84c5333cf0))
* **deploy:** Fix deploy for actual simple server ([b4cdd3c](https://github.com/melasistema/clients-melasistema/commit/b4cdd3c659ba498334293beacb99eaf5ceb708d7))

### [0.0.6](https://github.com/melasistema/clients-melasistema/compare/v0.0.5...v0.0.6) (2025-08-03)

### [0.0.5](https://github.com/melasistema/clients-melasistema/compare/v0.0.4...v0.0.5) (2025-08-03)


### Bug Fixes

* **deploy:** Fix cd to folder ([9dd8339](https://github.com/melasistema/clients-melasistema/commit/9dd8339f21a8f354f44056c8134d19c1829e44a2))

### [0.0.4](https://github.com/melasistema/clients-melasistema/compare/v0.0.3...v0.0.4) (2025-08-03)

### [0.0.3](https://github.com/melasistema/clients-melasistema/compare/v0.0.2...v0.0.3) (2025-08-03)

### 0.0.2 (2025-08-03)


### Features

* **clients:** Implement client CRUD functionality ([a900d09](https://github.com/melasistema/clients-melasistema/commit/a900d09afffb283a4c32d737cc7d2837b071cf59))
* Implement Project and Task Management with UI ([d89a151](https://github.com/melasistema/clients-melasistema/commit/d89a151a8de85d1188c8cbe7de3021417d1d7739))
* Implement User-Specific Clients, Project/Task Management, and Time Tracking ([b657982](https://github.com/melasistema/clients-melasistema/commit/b65798242dc0d0105a9939eebee28c2dcaa7532d))
* **release:** Add CI/CD ([fda1749](https://github.com/melasistema/clients-melasistema/commit/fda1749dbfb50c9ba2cdfcea90e09db2d66083bb))


### Bug Fixes

* **link:** change repo links ([9492e95](https://github.com/melasistema/clients-melasistema/commit/9492e95ce73d74aeb98aec096c7fc492c8f2cf1c))
* **release:** Fix application production directory ([0e7da0f](https://github.com/melasistema/clients-melasistema/commit/0e7da0fb6407196a5b8b18fec3aa7717b4ef215b))
