# Bubba4 — Deployer for Git staging installation

**Only install the isolated preview package. Do not deploy the full repository root or `plugin/bubba-hub-manager` as a second active plugin.**

The staging-safe WordPress plugin is located at `bubba4/bubba4.php` on branch `feature/bh4-compatibility-foundation`. Its folder/slug is `bubba4`. It loads no BH3 classes, does not register booking routes, and does not migrate tables.

Deployer for Git must support selecting a **subdirectory/package path** of `bubba4`, while retaining the WordPress plugin folder `bubba4`. Verify this option in the actual installed Deployer for Git UI/documentation before configuring a deployment. If the plugin only installs the whole repository root, **STOP**: the repository root contains a second plugin header and legacy BH3 code and is not an appropriate installation package. Use the isolated GitHub Actions ZIP artifact instead or arrange a separate dedicated preview repository.

Preflight:
1. Confirm target is `staging.bubbahub.co.uk`, not production; back up staging files and database.
2. Confirm existing BH3 plugin stays active; no PHP class, hook or shortcode should be replaced.
3. Set repository `bubbashub1/bubba4`, branch `feature/bh4-compatibility-foundation`, plugin slug `bubba4`, source directory `bubba4` **only if supported by the deployment plugin**.
4. Preview deployment destination and package file list before triggering. Expected only `bubba4/bubba4.php`.
5. Deploy and activate; expect only an informational admin notice for administrators. No directory, booking, family or leader feature is enabled.
6. Verify WordPress Site Health, existing BH3 activity directory, family/leader pages, booking checkout unchanged. On error deactivate Bubba4; do not deactivate BH3 or remove shared data.

No automated deployment hook or REST trigger is configured here. The branch remains draft and must not be merged for this preview.
