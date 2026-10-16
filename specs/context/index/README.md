# The Index — three files, three questions

The index exists so that a session **greps instead of explores**. Exploration
costs a dozen file reads and still misses things; a grep against a file designed
to be grepped costs one and does not.

| Question | File | Maintained |
|---|---|---|
| Where does X live, and what does it touch? | `inventory.md` | **Generated** |
| The ticket says "{business term}" — what is that here? | `vocabulary.md` | By hand |
| Who owns this behaviour, and why is it like that? | `map.md` | By hand |

The split is not tidiness. **Only one of the three can be generated**, and
pretending otherwise is how index files rot:

- A scanner can list every class, file or entity and the edges between them. It
  cannot know that users call it one thing and the code another, and it cannot
  know *why* a boundary sits where it does.
- Conversely, a hand-maintained inventory is stale within a month of the project
  moving fast, and nobody notices, because nobody reads an inventory end to end.

So: generate the inventory, hand-write the two things a generator cannot know,
and never duplicate one file's content into another.

## `inventory.md` — generated

Write the generator in whatever suits the stack. What matters is the contract:

**1. One line per thing, answering both questions at once.**

```
Name | path/under/src | Kind -> what it depends on
```

A single `grep PaymentService` must return where it lives **and** what it
touches, with no second lookup in a second file. Splitting those answers across
a paths file and a dependencies file was tried in the project these conventions
came from and cost roughly 22k tokens for the same information.

**2. It is grepped, not read.** Say so in the file's own header. A 400-line
inventory is an excellent grep target and a useless thing to load whole — and a
reader who does not know that will load it whole.

**3. Edges come from the source, never from naming.** An edge inferred from a
naming convention is a guess that looks like a fact.

**4. Drop edges that carry no information.** Framework scaffolding that imports
itself by construction inflates the file and tells a reader nothing the path
did not.

**5. The generator refuses.** This is what keeps it alive. A generator that
silently emits a partial result produces a file that is quietly wrong; one that
exits non-zero when something is unclassified — a new module nobody assigned, a
reference nobody categorised — forces the human decision that keeps the index
honest. Take counts from the source of truth (the database, the compiler, the
file system) rather than counting them off the artifact.

**6. Regenerate as part of the change that made it stale**, and commit the
result. A stale index is worse than no index: it sends a reader confidently to a
path that moved. There is no automated freshness check here and there is not
meant to be one — running the generator is a line in the definition of done, not
a pipeline someone else maintains.

## `vocabulary.md` — business language → our names

Read it before searching for anything by its business name. Its most valuable
section is the **traps** table: terms where the obvious guess is wrong, where
the plural is a different concept from the singular, or where one word means
three things. Those are exactly the cases where grep returns nothing and a
session concludes the feature does not exist.

A term goes in the first time somebody has to ask what it maps to. That question
is the signal, and it will be asked again.

## `map.md` — ownership and the why

Prose pointers: what owns a behaviour, what a diagram is authoritative for, and
the reasoning a scanner cannot produce. Deliberately **not machine-parsed** —
see the warning at the top of the file, which exists because the original
`.yaml` extension made readers expect a config file.

Add a row when something gains a fact worth recording, not because a file was
created. `map.md` is not an inventory.
