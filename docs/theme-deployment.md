# Theme deployment

Git is the source of truth for the `lfk-tailwind` theme. WordPress core, plugins,
uploads, database content, generated caches, and credentials are outside this
repository and outside this deployment workflow.

## Branches and environments

- `staging` deploys to `https://staging.learningforkidz.com` after Theme CI passes.
- `main` deploys the same commit to `https://learningforkidz.com` after Theme CI
  passes and the protected GitHub `production` environment is approved.
- Feature branches use pull requests and run Theme CI without deploying.

Each deploy creates an immutable release at:

```text
/home/thaiada/theme-releases/<environment>/lfk-tailwind/releases/<commit>
```

The WordPress theme path points to an atomic `current` symlink. The original
directory is retained as a timestamped `*.pre-git-*` backup during the first
migration.

## Required GitHub environment configuration

Both `staging` and `production` use these names:

- Variable `DEPLOY_HOST`
- Variable `DEPLOY_PORT`
- Secret `DEPLOY_SSH_KEY`
- Secret `DEPLOY_KNOWN_HOSTS`

The keys are separate and installed on the host with forced commands. A staging
key cannot deploy production, and a production key cannot deploy staging.

## Promotion

1. Commit changes on a feature branch.
2. Merge or push the approved commit to `staging`.
3. Verify the staging archive, product, cart, and checkout flows.
4. Promote the exact staging commit to `main`.
5. Approve the protected `production` GitHub environment deployment.
6. Smoke-test production and record the deployed commit.

Do not edit the live theme directory or deploy it with `rsync`. Roll back by
repointing `current` to a previously verified release and purging the LiteSpeed
cache.
