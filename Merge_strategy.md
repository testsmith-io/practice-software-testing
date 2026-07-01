# Merge Strategy
---

## 1. Create a feature branch for the user story

> On the remote we **only** want **one** branch *per user story*!

For Example:

```sh
git checkout -b 1008
```

## 2. Locally you can create more branches for tasks if needed

If you have multiple tasks or split the work or work on multiple devices, for example:

```sh
git checkout -b 1008-mytask
```

## 3. Commit your changes locally and merge into the userstory branch

If you have created individual branches for tasks, you commit there, otherwise directly to the user story branch.

> Make sure you have the **correct branch** checked out. For example with `git status`.

If you have individual branches, merge the **functioning** and **complete** work into the user story branch. For example:

```sh
git checkout 1008
git merge 1008-mytask
```

## 4. Push your local user story branch to the remote repository

On your first push you must link the branch with the remote repo. For example:

```sh
git push -u origin 1008
```

Every later push on that branch only needs regular `git push`.

## 5. Create GitHub pull request to merge the user story into `main`

Use the GitHub interface to create a *Pull Request* for **your user story** branch to merge it into `main`. 

> The *Pull Request* will be approved (or declined) by our Scrum Master Francesco or Elias or Michael.
